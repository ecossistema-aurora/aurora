<?php

declare(strict_types=1);

namespace App\Command;

use Exception;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:check-coverage',
    description: 'Checks that the test coverage meets the configured minimum threshold',
)]
class CheckCoverageCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        private float $lineMinimumThreshold = 50.0,
        private float $methodMinimumThreshold = 50.0,
        private float $branchMinimumThreshold = 50.0,
        private float $classMinimumThreshold = 50.0,
        private float $pathMinimumThreshold = 50.0,
    ) {
        parent::__construct();

        $this->lineMinimumThreshold = (float) $_ENV['LINE_COVERAGE'];
        $this->methodMinimumThreshold = (float) $_ENV['METHOD_COVERAGE'];
        $this->branchMinimumThreshold = (float) $_ENV['BRANCH_COVERAGE'];
        $this->classMinimumThreshold = (float) $_ENV['CLASS_COVERAGE'];
        $this->pathMinimumThreshold = (float) $_ENV['PATH_COVERAGE'];
    }

    protected function configure(): void
    {
        $this
            ->addArgument('coverage-file', InputArgument::OPTIONAL, 'Cover file (XML)', 'coverage.txt')
            ->addOption('line-threshold', 'lt', InputOption::VALUE_REQUIRED, 'Line minimum coverage threshold', $this->lineMinimumThreshold)
            ->addOption('method-threshold', 'mt', InputOption::VALUE_REQUIRED, 'Method minimum coverage threshold', $this->methodMinimumThreshold)
            ->addOption('branch-threshold', 'bt', InputOption::VALUE_REQUIRED, 'Branch minimum coverage threshold', $this->branchMinimumThreshold)
            ->addOption('class-threshold', 'ct', InputOption::VALUE_REQUIRED, 'Class minimum coverage threshold', $this->classMinimumThreshold)
            ->addOption('path-threshold', 'pt', InputOption::VALUE_REQUIRED, 'Path minimum coverage threshold', $this->pathMinimumThreshold)
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (table|json|text)', 'table')
            ->setHelp('
This command checks that the test coverage meets the configured minimum threshold.

<info>Usage examples:</info>
  <comment>php bin/console app:check-coverage</comment>
  <comment>php bin/console app:check-coverage --line-threshold=85</comment>
  <comment>php bin/console app:check-coverage coverage.xml --format=json</comment>
');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $coverageFile = $input->getArgument('coverage-file');
        $format = $input->getOption('format');

        $coverageFilePath = $this->resolveCoverageFilePath($coverageFile);

        if (!file_exists($coverageFilePath)) {
            $io->error("Cover file not found: {$coverageFilePath}");

            return Command::FAILURE;
        }

        try {
            $coverageData = $this->parseCoverageFile($coverageFilePath);
            $result = $this->checkCoverage($coverageData);

            $this->displayResults($io, $result, $format);

            return in_array(false, $result['passed'], true) ? Command::FAILURE : Command::SUCCESS;
        } catch (Exception $e) {
            $io->error("Error when processing coverage file: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    private function resolveCoverageFilePath(string $coverageFile): string
    {
        if (str_starts_with($coverageFile, '/')) {
            return $coverageFile;
        }

        return $this->projectDir.'/'.$coverageFile;
    }

    private function parseCoverageFile(string $coverageFilePath): array
    {
        $content = file_get_contents($coverageFilePath);
        if (false === $content) {
            throw new RuntimeException('The coverage text file could not be loaded');
        }

        $lines = explode("\n", $content);
        $lines = array_slice($lines, 0, 15);

        $coverageData = [];

        foreach ($lines as $line) {
            if (preg_match('/Lines:\s*([\d.]+)%\s*\((\d+)\/(\d+)\)/', $line, $matches)) {
                $coverageData['line_coverage'] = (float) $matches[1];
                $coverageData['covered_lines'] = (float) $matches[2];
                $coverageData['lines'] = (float) $matches[3];
            } elseif (preg_match('/Branches:\s*([\d.]+)%\s*\((\d+)\/(\d+)\)/', $line, $matches)) {
                $coverageData['branch_coverage'] = (float) $matches[1];
                $coverageData['covered_branches'] = (float) $matches[2];
                $coverageData['branches'] = (float) $matches[3];
            } elseif (preg_match('/Paths:\s*([\d.]+)%\s*\((\d+)\/(\d+)\)/', $line, $matches)) {
                $coverageData['path_coverage'] = (float) $matches[1];
                $coverageData['covered_paths'] = (float) $matches[2];
                $coverageData['paths'] = (float) $matches[3];
            } elseif (preg_match('/Classes:\s*([\d.]+)%\s*\((\d+)\/(\d+)\)/', $line, $matches)) {
                $coverageData['class_coverage'] = (float) $matches[1];
                $coverageData['covered_classes'] = (float) $matches[2];
                $coverageData['classes'] = (float) $matches[3];
            } elseif (preg_match('/Methods:\s*([\d.]+)%\s*\((\d+)\/(\d+)\)/', $line, $matches)) {
                $coverageData['method_coverage'] = (float) $matches[1];
                $coverageData['covered_methods'] = (float) $matches[2];
                $coverageData['methods'] = (float) $matches[3];
            }
        }

        if ([] === $coverageData) {
            throw new RuntimeException('No coverage data found in the text file');
        }

        return $coverageData;
    }

    private function checkCoverage(array $coverageData): array
    {
        if ([] === $coverageData) {
            throw new RuntimeException('No lines of code found for analysis');
        }

        return [
            'line_coverage' => $coverageData['line_coverage'],
            'branch_coverage' => $coverageData['branch_coverage'],
            'path_coverage' => $coverageData['path_coverage'],
            'method_coverage' => $coverageData['method_coverage'],
            'class_coverage' => $coverageData['class_coverage'],
            'passed' => [
                'line' => ($coverageData['line_coverage'] >= $this->lineMinimumThreshold),
                'branch' => ($coverageData['branch_coverage'] >= $this->branchMinimumThreshold),
                'path' => ($coverageData['path_coverage'] >= $this->pathMinimumThreshold),
                'method' => ($coverageData['method_coverage'] >= $this->methodMinimumThreshold),
                'class' => ($coverageData['class_coverage'] >= $this->classMinimumThreshold),
            ],
            'deficit' => [
                'line' => max(0, $this->lineMinimumThreshold - $coverageData['line_coverage']),
                'branch' => max(0, $this->branchMinimumThreshold - $coverageData['branch_coverage']),
                'path' => max(0, $this->pathMinimumThreshold - $coverageData['path_coverage']),
                'method' => max(0, $this->methodMinimumThreshold - $coverageData['method_coverage']),
                'class' => max(0, $this->classMinimumThreshold - $coverageData['class_coverage']),
            ],
            'minimum_threshold' => [
                'line' => $this->lineMinimumThreshold,
                'branch' => $this->branchMinimumThreshold,
                'path' => $this->pathMinimumThreshold,
                'method' => $this->methodMinimumThreshold,
                'class' => $this->classMinimumThreshold,
            ],
            'raw_data' => $coverageData,
        ];
    }

    private function displayResults(SymfonyStyle $io, array $result, string $format): void
    {
        switch ($format) {
            case 'json':
                $this->displayJsonResults($io, $result);
                break;
            case 'text':
                $this->displayTextResults($io, $result);
                break;
            default:
                $this->displayTableResults($io, $result);
        }
    }

    private function displayTableResults(SymfonyStyle $io, array $result): void
    {
        $io->title('Test Coverage Report');

        $io->table(
            ['Metric', 'Coverage', 'Total', 'Percentage', 'Threshold'],
            [
                [
                    'Lines',
                    number_format($result['raw_data']['covered_lines']),
                    number_format($result['raw_data']['lines']),
                    number_format($result['line_coverage'], 2).'%',
                    number_format($result['minimum_threshold']['line'], 2).'%',
                ],
                [
                    'Branches',
                    number_format($result['raw_data']['covered_branches']),
                    number_format($result['raw_data']['branches']),
                    number_format($result['branch_coverage'], 2).'%',
                    number_format($result['minimum_threshold']['branch'], 2).'%',
                ],
                [
                    'Paths',
                    number_format($result['raw_data']['covered_paths']),
                    number_format($result['raw_data']['paths']),
                    number_format($result['path_coverage'], 2).'%',
                    number_format($result['minimum_threshold']['path'], 2).'%',
                ],
                [
                    'Methods',
                    number_format($result['raw_data']['covered_methods']),
                    number_format($result['raw_data']['methods']),
                    number_format($result['method_coverage'], 2).'%',
                    number_format($result['minimum_threshold']['method'], 2).'%',
                ],
                [
                    'Classes',
                    number_format($result['raw_data']['covered_classes']),
                    number_format($result['raw_data']['classes']),
                    number_format($result['class_coverage'], 2).'%',
                    number_format($result['minimum_threshold']['class'], 2).'%',
                ],
            ]
        );

        $io->section('Result of the check');

        foreach ($result['passed'] as $key => $value) {
            if (true === $value) {
                $io->success(sprintf(
                    "{$key} coverage approved! %.2f%% >= %.2f%% (deficit: %.2f%% below the minimum threshold)",
                    $result["{$key}_coverage"],
                    $result['minimum_threshold'][$key],
                    $result['deficit'][$key]
                ));
            } else {
                $io->error(sprintf(
                    "{$key} insufficient coverage! %.2f%% < %.2f%% (deficit: %.2f%% below the minimum threshold)",
                    $result["{$key}_coverage"],
                    $result['minimum_threshold'][$key],
                    $result['deficit'][$key]
                ));
            }
        }
    }

    private function displayTextResults(SymfonyStyle $io, array $result): void
    {
        $io->writeln(
            sprintf(
                "Current coverage:\n - line: %.2f%%;\n - branch: %.2f%%;\n - path: %.2f%%;\n - method: %.2f%%;\n - class: %.2f%%.\n",
                $result['line_coverage'],
                $result['branch_coverage'],
                $result['path_coverage'],
                $result['method_coverage'],
                $result['class_coverage'],
            )
        );
        $io->writeln(
            sprintf(
                "Minimum threshold:\n - line: %.2f%%;\n - branch: %.2f%%;\n - path: %.2f%%;\n - method: %.2f%%;\n - class: %.2f%%.\n",
                $result['minimum_threshold']['line'],
                $result['minimum_threshold']['branch'],
                $result['minimum_threshold']['path'],
                $result['minimum_threshold']['method'],
                $result['minimum_threshold']['class'],
            )
        );
        $io->writeln(sprintf('Status: %s', $result['passed'] ? 'APPROVED' : 'FAILED'));

        foreach ($result as $key => $value) {
            if (false === $value) {
                $io->writeln(sprintf('Deficit: %.2f%%', $result['deficit'][$key]));
            }
        }
    }

    private function displayJsonResults(SymfonyStyle $io, array $result): void
    {
        $io->writeln(json_encode([
            'current_coverage' => [
                'line' => $result['line_coverage'],
                'branch' => $result['branch_coverage'],
                'path' => $result['path_coverage'],
                'method' => $result['method_coverage'],
                'class' => $result['class_coverage'],
            ],
            'minimum_threshold' => $result['minimum_threshold'],
            'passed' => $result['passed'],
            'deficit' => $result['deficit'],
        ], JSON_PRETTY_PRINT));
    }
}
