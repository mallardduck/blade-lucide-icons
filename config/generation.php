<?php

use BladeUI\Icons\Generation\IconSetConfig;

$svgNormalization = static function (
    string $tempFilepath,
    IconSetConfig $iconSet,
    SplFileInfo $file
) : void {
    $doc = new DOMDocument();
    $doc->load($tempFilepath);
    $svgElement = $doc->getElementsByTagName('svg')[0];
    $svgElement->removeAttribute('width');
    $svgElement->removeAttribute('height');
    $doc->save($tempFilepath);

    $fileLines = file($tempFilepath);
    array_shift($fileLines);

    $lastKey = count($fileLines) - 1;
    $fileLines[$lastKey] = trim($fileLines[$lastKey]);
    file_put_contents($tempFilepath, $fileLines);
};

return [
    IconSetConfig::build(
        source: __DIR__.'/../lucide/icons',
        destination: __DIR__.'/../resources/svg',
        safe: true,
        after: $svgNormalization,
    ),
];
