<#
.SYNOPSIS
  Build a clean, distributable CosyPaw theme zip.

.DESCRIPTION
  Copies only the runtime theme (PHP templates, inc/, built dist/, languages/,
  and image assets) into build/cosypaw, then zips it to build/cosypaw.zip.

  Excludes everything dev/source: node_modules, vendor, tests, tools, Composer/
  npm/Vite config, source assets/css + assets/js, the hot file, plus the design
  scratch (claude/, _ds/, uploads/, *.dc.html, support.js, *.cache).

  Run:  npm run build   (first, so dist/ is current)
        powershell -File tools/package-theme.ps1

  NOTE: dist/ is enqueued at runtime by inc/Assets.php -- always rebuild it
  before packaging, since assets/css + assets/js source are NOT shipped.
#>

$ErrorActionPreference = 'Stop'
$root  = Split-Path -Parent $PSScriptRoot
$build = Join-Path $root 'build'
$stage = Join-Path $build 'cosypaw'
$zip   = Join-Path $build 'cosypaw.zip'

if (-not (Test-Path (Join-Path $root 'dist\.vite\manifest.json'))) {
	throw "dist/.vite/manifest.json missing. Run 'npm run build' before packaging."
}

if (Test-Path $build) { Remove-Item $build -Recurse -Force }
New-Item -ItemType Directory -Force -Path $stage | Out-Null

# Top-level template + config files that ship.
$files = @(
	'style.css','index.php','functions.php','theme.json','comments.php',
	'header.php','footer.php','front-page.php','page.php','single.php',
	'archive.php','home.php','search.php','404.php','searchform.php','sidebar.php'
)
foreach ($f in $files) {
	$src = Join-Path $root $f
	if (Test-Path $src) { Copy-Item $src (Join-Path $stage $f) -Force }
}

# Directories that ship as-is.
foreach ($d in 'inc','template-parts','dist','languages') {
	$src = Join-Path $root $d
	if (Test-Path $src) { Copy-Item $src (Join-Path $stage $d) -Recurse -Force }
}

# Image assets only. The css/js source is compiled into dist/ and not shipped.
$assetsOut = Join-Path $stage 'assets'
New-Item -ItemType Directory -Force -Path $assetsOut | Out-Null
$imgExt = '.png','.jpg','.jpeg','.gif','.svg','.webp'
Get-ChildItem (Join-Path $root 'assets') -File |
	Where-Object { $imgExt -contains $_.Extension.ToLower() } |
	ForEach-Object { Copy-Item $_.FullName (Join-Path $assetsOut $_.Name) -Force }

# Zip it.
if (Test-Path $zip) { Remove-Item $zip -Force }
Compress-Archive -Path $stage -DestinationPath $zip -Force

$size  = [math]::Round((Get-Item $zip).Length / 1KB, 1)
$count = (Get-ChildItem $stage -Recurse -File).Count
Write-Output "Packaged: $zip ($size KB, $count files)"
