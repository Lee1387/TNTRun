<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$pluginYmlPath = $projectRoot . DIRECTORY_SEPARATOR . 'plugin.yml';

if (!is_file($pluginYmlPath)) {
    fwrite(STDERR, "plugin.yml was not found.\n");
    exit(1);
}

if (ini_get('phar.readonly') === '1') {
    fwrite(STDERR, "phar.readonly is enabled. Run with: php -d phar.readonly=0 tools/build-plugin.php\n");
    exit(1);
}

$pluginYaml = file_get_contents($pluginYmlPath);
if ($pluginYaml === false) {
    fwrite(STDERR, "Failed to read plugin.yml.\n");
    exit(1);
}

if (!preg_match('/^name:\s*(.+)$/m', $pluginYaml, $nameMatch)) {
    fwrite(STDERR, "Unable to read plugin name from plugin.yml.\n");
    exit(1);
}

$pluginName = trim($nameMatch[1]);
$buildDirectory = $projectRoot . DIRECTORY_SEPARATOR . 'build';
$pharPath = $buildDirectory . DIRECTORY_SEPARATOR . $pluginName . '.phar';

if (!is_dir($buildDirectory) && !mkdir($buildDirectory, 0777, true) && !is_dir($buildDirectory)) {
    fwrite(STDERR, "Failed to create build directory.\n");
    exit(1);
}

if (is_file($pharPath) && !unlink($pharPath)) {
    fwrite(STDERR, "Failed to remove existing phar.\n");
    exit(1);
}

$phar = new Phar($pharPath, 0, basename($pharPath));
$phar->startBuffering();
$phar->setSignatureAlgorithm(Phar::SHA256);

addFileToPhar($phar, $projectRoot, 'plugin.yml');
addDirectoryToPhar($phar, $projectRoot, 'src');
addDirectoryToPhar($phar, $projectRoot, 'resources');

$phar->setStub("<?php __HALT_COMPILER();");
$phar->stopBuffering();

fwrite(STDOUT, "Built {$pharPath}\n");

/**
 * @param Phar $phar
 */
function addFileToPhar(Phar $phar, string $projectRoot, string $relativePath): void
{
    $absolutePath = $projectRoot . DIRECTORY_SEPARATOR . $relativePath;

    if (!is_file($absolutePath)) {
        return;
    }

    $phar->addFile($absolutePath, str_replace('\\', '/', $relativePath));
}

/**
 * @param Phar $phar
 */
function addDirectoryToPhar(Phar $phar, string $projectRoot, string $relativePath): void
{
    $absolutePath = $projectRoot . DIRECTORY_SEPARATOR . $relativePath;

    if (!is_dir($absolutePath)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $filePath = $file->getPathname();
        $relativeFilePath = substr($filePath, strlen($projectRoot) + 1);

        $phar->addFile($filePath, str_replace('\\', '/', $relativeFilePath));
    }
}