<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class TranslationsTest extends KernelTestCase
{
    private const array TRANSLATION_FILES = [
        'translations/messages.pt-br.yaml',
        'translations/messages.en.yaml',
        'translations/messages.es.yaml',
    ];

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testTranslationsFilesExists(): void
    {
        foreach (self::TRANSLATION_FILES as $file) {
            $this->assertFileExists(self::$kernel->getProjectDir().'/'.$file);
        }
    }

    public function testTranslationsHaveSameKeys(): void
    {
        $translations = [];
        foreach (self::TRANSLATION_FILES as $file) {
            $translations[$file] = Yaml::parseFile(self::$kernel->getProjectDir().'/'.$file);
        }

        foreach (self::TRANSLATION_FILES as $sourceFile) {
            foreach (self::TRANSLATION_FILES as $targetFile) {
                if ($sourceFile === $targetFile) {
                    continue;
                }

                $this->testKeyExists($translations[$sourceFile], $translations[$targetFile], $targetFile, '', $sourceFile);
            }
        }
    }

    private function testKeyExists(array $firstArray, array $secondArray, string $targetFileName = '', string $prefix = '', string $sourceFileName = ''): void
    {
        foreach ($firstArray as $key => $value) {
            $fullKey = $prefix ? $prefix.'.'.$key : $key;
            $this->assertArrayHasKey(
                $key,
                $secondArray,
                "The key '$fullKey' exists in '$sourceFileName' but is missing in '$targetFileName'."
            );

            if (is_array($value)) {
                $this->testKeyExists($value, $secondArray[$key] ?? [], $targetFileName, $fullKey, $sourceFileName);
            }
        }
    }
}
