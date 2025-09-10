<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportProjectsFromJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:projects-json {--file=database/data/nrca-projects.json} {--dry-run : Show what would be imported without actually importing} {--skip-assets : Skip downloading assets} {--chunk-size=10 : Number of projects to process at once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import projects from JSON file into the database';

    /**
     * Base URL for downloading assets
     */
    protected $baseAssetUrl = 'https://nrca-projects.web.app/';

    /**
     * Category mapping from JSON field names to category names
     */
    protected $categoryMap = [
        'capStr' => 'CAP',
        'ctpStr' => 'CTP', 
        'edsStr' => 'EDS',
        'biodiversityStr' => 'Biodiversity',
        'communityScienceStr' => 'Community Science',
        'climateChangeStr' => 'Climate Change',
        'foodWasteStr' => 'Food Waste',
        'forestryStr' => 'Forestry',
        'greenInfrastructureStr' => 'Green Infrastructure',
        'humanDimensionsStr' => 'Human Dimensions',
        'invasiveSpeciesStr' => 'Invasive Species',
        'landUseStr' => 'Land Use',
        'mappingGisStr' => 'Mapping/GIS',
        'OtherStr' => 'Other',
        'publicOutreachStr' => 'Public Outreach',
        'restorationStr' => 'Restoration',
        'soilsAgricultureStr' => 'Soils/Agriculture',
        'trailbuildingStr' => 'Trail Building',
        'waterQualityStr' => 'Water Quality',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->option('file');
        
        if (!file_exists(base_path($filePath))) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Starting import from {$filePath}...");

        // Read and decode JSON file
        $jsonContent = file_get_contents(base_path($filePath));
        $projects = json_decode($jsonContent, true);

        if (!$projects) {
            $this->error('Failed to parse JSON file');
            return 1;
        }

        $this->info('Found ' . count($projects) . ' projects to import');

        // Check for dry run
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->info('DRY RUN MODE - No data will be imported');
        }

        // Get chunk size
        $chunkSize = (int) $this->option('chunk-size');
        $this->info("Processing in chunks of {$chunkSize} projects");

        // Create categories if they don't exist
        $this->createCategories($isDryRun);

        // Process projects in chunks to manage memory
        $totalProjects = count($projects);
        $chunks = array_chunk($projects, $chunkSize);
        $totalChunks = count($chunks);
        
        $importedCount = 0;
        $skippedCount = 0;

        foreach ($chunks as $chunkIndex => $chunk) {
            $this->info("Processing chunk " . ($chunkIndex + 1) . "/{$totalChunks}...");
            
            if (!$isDryRun) {
                DB::beginTransaction();
            }

            try {
                foreach ($chunk as $index => $projectData) {
                    $globalIndex = ($chunkIndex * $chunkSize) + $index + 1;
                    
                    // Skip projects with empty titles
                    if (empty($projectData['projectTitle']) || trim($projectData['projectTitle']) === '') {
                        $this->warn("Skipping project with empty title at index {$globalIndex}");
                        $skippedCount++;
                        continue;
                    }

                    $this->info("Processing project {$globalIndex}/{$totalProjects}: " . $projectData['projectTitle']);

                    // Check if project already exists
                    $existingProject = Project::where('title', $projectData['projectTitle'])
                        ->where('year', $projectData['year'])
                        ->first();

                    if ($existingProject) {
                        $this->warn("Skipping duplicate project: " . $projectData['projectTitle']);
                        $skippedCount++;
                        continue;
                    }

                    if ($isDryRun) {
                        $this->info("Would import: " . $projectData['projectTitle'] . " (" . $projectData['year'] . ")");
                        $importedCount++;
                        continue;
                    }

                    // Create project
                    $project = $this->createProject($projectData, $isDryRun);
                    
                    // Attach categories
                    $this->attachCategories($project, $projectData);

                    $importedCount++;

                    // Force garbage collection periodically
                    if ($importedCount % 5 == 0) {
                        gc_collect_cycles();
                    }
                }

                if (!$isDryRun) {
                    DB::commit();
                    $this->info("Chunk " . ($chunkIndex + 1) . " committed successfully");
                }

                // Show progress
                $this->info("Progress: {$importedCount} imported, {$skippedCount} skipped");
                
                // Force garbage collection after each chunk
                gc_collect_cycles();
                
            } catch (\Exception $e) {
                if (!$isDryRun) {
                    DB::rollBack();
                }
                $this->error("Chunk " . ($chunkIndex + 1) . " failed: " . $e->getMessage());
                return 1;
            }
        }

        
        $this->info("Import completed successfully!");
        $this->info("Imported: {$importedCount} projects");
        $this->info("Skipped: {$skippedCount} duplicate projects");

        return 0;
    }

    /**
     * Create categories that don't exist
     */
    protected function createCategories($isDryRun = false)
    {
        $this->info('Creating categories...');

        foreach ($this->categoryMap as $categoryName) {
            if ($isDryRun) {
                $existing = Category::where('name', $categoryName)->first();
                if (!$existing) {
                    $this->info("Would create category: {$categoryName}");
                }
            } else {
                Category::firstOrCreate([
                    'name' => $categoryName
                ], [
                    'description' => "Imported category: {$categoryName}"
                ]);
            }
        }

        $this->info('Categories created/verified');
    }

    /**
     * Create a project from JSON data
     */
    protected function createProject(array $projectData, bool $isDryRun = false): Project
    {
        // Map county from JSON format to proper format
        $county = $this->formatCounty($projectData['countyStr']);
        
        // Determine program based on the category strings
        $program = $this->determineProgram($projectData);

        // Handle multiple URLs in projectUrl field
        $urls = $this->parseProjectUrls($projectData['projectUrl'] ?? '');

        // Download and store assets
        $thumbnailPath = $this->downloadAndStoreThumbnail($projectData['thumbnailImageUrl'] ?? '', $isDryRun);
        $pdfPath = $this->downloadAndStorePdf($projectData['pdfFileUrl'] ?? '', $isDryRun);

        $project = Project::create([
            'title' => $projectData['projectTitle'],
            'year' => $projectData['year'],
            'county' => $county,
            'program' => $program,
            'thumbnail' => $thumbnailPath,
            'primary_product' => $pdfPath,
            'primary_product_url' => $urls['primary'] ?? null,
            'secondary_product_url' => $urls['secondary'] ?? null,
            'third_download_url' => $urls['third'] ?? null,
        ]);

        return $project;
    }

    /**
     * Attach categories to project based on JSON data
     */
    protected function attachCategories(Project $project, array $projectData)
    {
        $categoryIds = [];

        foreach ($this->categoryMap as $jsonField => $categoryName) {
            // Check if this category field has a value
            if (!empty($projectData[$jsonField])) {
                $category = Category::where('name', $categoryName)->first();
                if ($category) {
                    $categoryIds[] = $category->id;
                }
            }
        }

        if (!empty($categoryIds)) {
            $project->categories()->attach($categoryIds);
        }
    }

    /**
     * Format county name from JSON format to proper format
     */
    protected function formatCounty(string $countyStr): string
    {
        // Handle multiple counties separated by commas
        $counties = explode(',', $countyStr);
        $formattedCounties = [];

        foreach ($counties as $county) {
            $county = trim($county);
            // Convert from lowercase to proper case
            $formattedCounties[] = ucwords(str_replace(['_', '-'], ' ', $county));
        }

        return implode(', ', $formattedCounties);
    }

    /**
     * Determine program based on category strings
     */
    protected function determineProgram(array $projectData): string
    {
        if (!empty($projectData['capStr'])) {
            return 'CAP';
        } elseif (!empty($projectData['ctpStr'])) {
            return 'CTP';
        } elseif (!empty($projectData['edsStr'])) {
            return 'EDS';
        }
        
        return 'Unknown';
    }

    /**
     * Parse multiple URLs from projectUrl field
     */
    protected function parseProjectUrls(string $projectUrl): array
    {
        if (empty($projectUrl)) {
            return [];
        }

        $urls = explode(',', $projectUrl);
        $result = [];

        foreach ($urls as $index => $url) {
            $url = trim($url);
            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                if ($index === 0) {
                    $result['primary'] = $url;
                } elseif ($index === 1) {
                    $result['secondary'] = $url;
                } elseif ($index === 2) {
                    $result['third'] = $url;
                }
            }
        }

        return $result;
    }

    /**
     * Download and store thumbnail image
     */
    protected function downloadAndStoreThumbnail(string $thumbnailUrl, bool $isDryRun = false): ?string
    {
        if (empty($thumbnailUrl)) {
            return null;
        }

        // Skip if user wants to skip assets
        if ($this->option('skip-assets')) {
            $this->info("Skipping asset download: {$thumbnailUrl}");
            return null;
        }

        // Check if URL has protocol, if not, prepend base URL
        if (!parse_url($thumbnailUrl, PHP_URL_SCHEME)) {
            $fullUrl = $this->baseAssetUrl . ltrim($thumbnailUrl, '/');
        } else {
            $fullUrl = $thumbnailUrl;
        }

        if ($isDryRun) {
            $this->info("Would download thumbnail: {$fullUrl}");
            return "thumbnails/" . basename($thumbnailUrl);
        }

        try {
            // Generate filename
            $filename = basename($thumbnailUrl);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Ensure we have an extension
            if (empty($extension)) {
                $extension = 'jpg'; // default extension
                $filename .= '.jpg';
            }

            // Create unique filename to avoid conflicts
            $storagePath = 'thumbnails/' . Str::random(8) . '_' . $filename;

            $this->info("Downloading thumbnail from: {$fullUrl}");
            
            // Download the file with timeout
            $response = Http::timeout(30)->get($fullUrl);
            
            if ($response->successful()) {
                // Store the file
                Storage::disk('public')->put($storagePath, $response->body());
                $this->info("Thumbnail stored at: {$storagePath}");
                
                // Clear the response from memory
                unset($response);
                gc_collect_cycles();
                
                return $storagePath;
            } else {
                $this->warn("Failed to download thumbnail: {$fullUrl} (HTTP {$response->status()})");
                return null;
            }
        } catch (\Exception $e) {
            $this->warn("Error downloading thumbnail {$fullUrl}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Download and store PDF file
     */
    protected function downloadAndStorePdf(string $pdfUrl, bool $isDryRun = false): ?string
    {
        if (empty($pdfUrl)) {
            return null;
        }

        // Skip if user wants to skip assets
        if ($this->option('skip-assets')) {
            $this->info("Skipping asset download: {$pdfUrl}");
            return null;
        }

        // Check if URL has protocol, if not, prepend base URL
        if (!parse_url($pdfUrl, PHP_URL_SCHEME)) {
            $fullUrl = $this->baseAssetUrl . ltrim($pdfUrl, '/');
        } else {
            $fullUrl = $pdfUrl;
        }

        if ($isDryRun) {
            $this->info("Would download PDF: {$fullUrl}");
            return "products/" . basename($pdfUrl);
        }

        try {
            // Generate filename
            $filename = basename($pdfUrl);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Ensure we have an extension
            if (empty($extension)) {
                $extension = 'pdf'; // default extension
                $filename .= '.pdf';
            }

            // Create unique filename to avoid conflicts
            $storagePath = 'products/' . Str::random(8) . '_' . $filename;

            $this->info("Downloading PDF from: {$fullUrl}");
            
            // Download the file with timeout
            $response = Http::timeout(60)->get($fullUrl); // Longer timeout for PDFs
            
            if ($response->successful()) {
                // Store the file
                Storage::disk('public')->put($storagePath, $response->body());
                $this->info("PDF stored at: {$storagePath}");
                
                // Clear the response from memory
                unset($response);
                gc_collect_cycles();
                
                return $storagePath;
            } else {
                $this->warn("Failed to download PDF: {$fullUrl} (HTTP {$response->status()})");
                return null;
            }
        } catch (\Exception $e) {
            $this->warn("Error downloading PDF {$fullUrl}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Map thumbnail image URL to storage path (legacy method)
     */
    protected function mapThumbnail(string $thumbnailUrl): ?string
    {
        if (empty($thumbnailUrl)) {
            return null;
        }

        // Extract filename from path like "images/project_thumbnails/cap_2012_1.jpg"
        $filename = basename($thumbnailUrl);
        
        // Return the path that would be used in storage
        return "thumbnails/{$filename}";
    }

    /**
     * Map PDF file URL to storage path (legacy method)
     */
    protected function mapPdfFile(string $pdfUrl): ?string
    {
        if (empty($pdfUrl)) {
            return null;
        }

        // Extract filename from path like "posters/cap_2012_1.pdf"
        $filename = basename($pdfUrl);
        
        // Return the path that would be used in storage
        return "products/{$filename}";
    }
}
