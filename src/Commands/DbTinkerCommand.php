<?php

namespace JamesClark32\DbTinker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JamesClark32\DbTinker\DbWrapper;
use JamesClark32\DbTinker\History;
use JamesClark32\DbTinker\InputWrapper;
use JamesClark32\DbTinker\OutputWrapper;
use JamesClark32\DbTinker\Query;

class DbTinkerCommand extends Command
{
    protected $description = 'Launches the user into an interactive database shell';
    protected $signature = 'db:tinker';
    protected DbWrapper $dbWrapper;
    protected InputWrapper $inputWrapper;
    protected OutputWrapper $outputWrapper;
    protected Query $query;
    protected History $history;
    protected string $connection;
    protected int $signalCount = 0;

    public function __construct(
        DbWrapper $dbWrapper,
        History $history,
        InputWrapper $inputWrapper,
        OutputWrapper $outputWrapper
    ) {
        $this->dbWrapper = $dbWrapper;
        $this->history = $history;
        $this->inputWrapper = $inputWrapper;
        $this->outputWrapper = $outputWrapper;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->initializeDbTinkerCommand();

        $this->output->writeln(trans('db-tinker::output.startup'));
        $this->output->writeln(trans('db-tinker::output.startup_exit'));
        $this->output->newLine();

        $this->testConnection();

        while (true) {
            $this->handleIteration();
        }
    }

    protected function initializeDbTinkerCommand(): void
    {
        ini_set('memory_limit', '1G');

        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, [$this, 'handelSignal']);
        pcntl_signal(SIGINT, [$this, 'handelSignal']);

        $this->connection = DB::getDefaultConnection();
        $this->outputWrapper->setOutput($this->output);
        $this->history->loadHistory();
        $this->inputWrapper->setHistory($this->history);
    }

    //@TODO: migrate this to input project
    public function handelSignal(int $signalNumber, $signalInformation)
    {
        $this->signalCount += 1;

        if ($this->signalCount > 10) {
            $this->output->writeln(trans('db-tinker::output.startup_exit'));
            $this->signalCount = 0;
        }
    }

    protected function handleIteration(): void
    {
        $queries = $this->inputWrapper->setConnectionName($this->connection)->getUserInput();

        foreach ($queries as $query) {

            $this->query = $query;

            $this->reconnectIfShould();

            if ($this->query->getNormalizedQueryText()) {

                if ($this->query->getNormalizedQueryText() === 'exit') {

                    $this->outputWrapper->outputExit();

                    exit;
                }

                $this->processQuery();
            }
        }
    }

    protected function reconnectIfShould(): void
    {
        $this->testConnection();

        if (!DB::connection()->getDatabaseName()) {
            $this->outputWrapper->outputReconnecting();
            DB::reconnect();
            $this->testConnection();
        }
    }

    protected function processQuery(): void
    {
        $results = $this->dbWrapper->setQuery($this->query)->execute();
        if (!$results) {
            $results = [];
        }

        $this->outputWrapper
            ->setOutput($this->output)
            ->setProcessingTime($this->dbWrapper->getProcessingTime())
            ->setQuery($this->query)
            ->setResults($results);

        $this->outputWrapper->render();
    }

    protected function testConnection(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->outputWrapper->setResults([
                'error' => [
                    'errorCode' => $e->getCode(),
                    'errorNumber' => $e->getCode(),
                    'errorMessage' => $e->getMessage(),
                ],
            ])->outputError();

            $this->output->warning(trans('db-tinker::output.connection_error'));
            exit;
        }
    }
}
