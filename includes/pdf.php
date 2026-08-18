<?php

function eams_mpdf(array $extra = []): \Mpdf\Mpdf
{
    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $fontDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'Noto_Sans_SC';

    $config = array_merge([
        'mode' => 'utf-8',
        'format' => 'A4',
        'fontDir' => array_merge($fontDirs, [$fontDir]),
        'fontdata' => $fontData + [
            'notosanssc' => [
                'R' => 'NotoSansSC-Regular.otf',
                'B' => 'NotoSansSC-Bold.otf',
            ],
            'notosanstc' => [
                'R' => 'NotoSansTC-Regular.otf',
                'B' => 'NotoSansTC-Regular.otf',
            ],
        ],
        'default_font' => 'notosanssc',
        'backupSubsFont' => ['notosanstc', 'dejavusanscondensed'],
    ], $extra);

    return new \Mpdf\Mpdf($config);
}
