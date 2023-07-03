<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use SwaggerLume\SecurityDefinitions;

class Generator
{
    public static function generateDocs($version = null)
    {
        $appDir = config('swagger-lume.paths.annotations');
        $docDir = config('swagger-lume.paths.docs');
        if (! File::exists($docDir) || is_writable($docDir)) {
            // delete all existing documentation
            if (File::exists($docDir)) {
                File::deleteDirectory($docDir);
            }

            self::defineConstants(config('swagger-lume.constants') ?: []);

            File::makeDirectory($docDir);

            if($version == null) $version = 1;

            $excludeDirs = array_diff( array_filter(glob('app/Http/Controllers/V*'), 'is_dir'), array_filter(glob('app/Http/Controllers/V'.$version), 'is_dir') );

            $filename = $docDir.'/api-v'.$version.'-docs.json';

            if (version_compare(config('swagger-lume.swagger_version'), '3.0', '>=')) {
                $swagger = \OpenApi\scan($appDir, ['exclude' => $excludeDirs]);
            } else {
                $swagger = \Swagger\scan($appDir, ['exclude' => $excludeDirs]);
            }

            if (config('swagger-lume.paths.base') !== null) {
                $swagger->basePath = config('swagger-lume.paths.base');
            }

            
            $swagger->saveAs($filename);

            $security = new SecurityDefinitions();
            $security->generate($filename);
        }
    }

    protected static function defineConstants(array $constants)
    {
        if (! empty($constants)) {
            foreach ($constants as $key => $value) {
                defined($key) || define($key, $value);
            }
        }
    }
}
