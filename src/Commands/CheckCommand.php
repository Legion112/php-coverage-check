<?php
declare(strict_types=1);

namespace Legion112\CoverageCheck\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckCommand extends Command
{
    private ?\SimpleXMLElement $config = null;

    public function __construct()
    {
        parent::__construct('check');
    }

    protected function configure():void
    {
        $this->setDescription(<<<DESCRIPTION
Command check if coverage report better then curren one saved previously in file and commited to repo.
If it worse will return error code. 
If it better and option "--save-improvement" passed then will override value in config with the better one. 
You need to commit the config file change in your CI. `git add coverage-check.xml && git commit -m"Code coverage improved" && git push`
DESCRIPTION
        );
        $this->addArgument('path-to-report-file', InputArgument::REQUIRED, 'The main index.xml file the report is stored');
        $this->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Xml file there coverage-check going to keep current value of coverage', 'coverage-check.xml');
        $this->addOption('save-improvement', 's', InputOption::VALUE_NONE,  'Save the new coverage value if it is better then curren one');
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $reportCoverage = $this->getReportCoveragePercent($input, $output);
        $currentCoverage = $this->getCoveragePercentFromConfig($input, $output);
        $worse = $reportCoverage < $currentCoverage;
        $better = $reportCoverage > $currentCoverage;
        if (!$worse && !$better) {
            $output->writeln('No coverage change was detected');
            return self::SUCCESS;
        }
        if ($worse) {
            $output->writeln(sprintf('<fg=red>New coverage report(%s%%) is worse then current one(%s%%)</>', $reportCoverage, $currentCoverage));
            $output->writeln('Add test that would increase coverage');
            return self::FAILURE;
        }
        $better && $output->writeln(
            sprintf('<fg=green>New coverage report(%s%%) is better then current one(%s%%) difference (%s%%)</>',
                $reportCoverage,
                $currentCoverage,
                $reportCoverage - $currentCoverage
            )
        );
        if ($better && $input->getOption('save-improvement') ) {
            $this->saveImprovement($reportCoverage, $input, $output);
            $output->writeln(sprintf('<fg=green>New coverage value(%s%%) has been saved to config</>', $reportCoverage));
            $output->writeln(sprintf('<fg=white>Commit change in config file (%s) to fix the new value</>', $this->getConfigPath($input)));
        }
        if ($better && ! $input->getOption('save-improvement')) {
            $output->writeln('<fg=white>Use --save-improvement to persist better coverage to config file</>');
        }

        return self::SUCCESS;
    }

    private function getReportCoveragePercent(InputInterface $input, OutputInterface $output):float
    {
        $path = $input->getArgument('path-to-report-file');
        $output->isDebug() && $output->writeln(sprintf('Report path: %s', $path));
        $xml = simplexml_load_string(file_get_contents($path));
        $coverageValue = (float)$xml->project->directory->totals->lines['percent'];
        $output->isDebug() && $output->writeln(sprintf('Value from report: %s%%', $coverageValue));
        return $coverageValue;
    }

    private function getCoveragePercentFromConfig(InputInterface $input, OutputInterface $output):float
    {
        $xml = $this->getConfigXml($input);
        return (float)$xml->current['percent'];
    }

    private function saveImprovement(float $reportCoverage, InputInterface $input, OutputInterface $output):void
    {
        $config = $this->getConfigXml($input);
        $current =  $config->current;

        $old = $config->history->addChild('report');
        $old->addAttribute('percent', (string)$current['percent']);
        $old->addAttribute('date', (string)$current['date']);
        $current['percent'] = $reportCoverage;
        $current['date'] = $this->getDateTime();
        $this->saveConfigXml($input, $output);
    }

    protected function getConfigXml(InputInterface $input): \SimpleXMLElement
    {
        $path = $this->getConfigPath($input);
        if (file_exists($path)) {
            $configContent = file_get_contents($path);
        } else {
            $configContent = <<<INIT_CONFIG
<?xml version="1.0" encoding="UTF-8"?>
<coverage>
    <current percent="0" date="{$this->getDateTime()}"/>
    <history>
    </history>
</coverage>
INIT_CONFIG;
        }
        if ($this->config === null) {
            $this->config = simplexml_load_string($configContent);
        }
        return $this->config;
    }

    protected function saveConfigXml(InputInterface $input, OutputInterface $output):void
    {
        $config = $this->getConfigXml($input);
        $config->asXML($this->getConfigPath($input));
    }

    /**
     * @param InputInterface $input
     * @return mixed
     */
    protected function getConfigPath(InputInterface $input): mixed
    {
        return $input->getOption('config');
    }

    /**
     * @return string
     */
    protected function getDateTime(): string
    {
        return date('Y-m-d\TH:i:sp');
    }
}