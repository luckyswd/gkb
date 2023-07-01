<?php

namespace googleApi;

use Google\Client;
use Google\Exception;
use Google\Service\Sheets;
use Google\Service\Sheets\ClearValuesRequest;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GoogleClient
{
    const RANGE = 'Лист1';

    private Sheets $sheetsService;

    public function __construct()
    {
        $this->sheetsService = new Sheets($this->getClient());
    }

    public function init(): void {
        try {
            $this->clearExel();
            $this->updateExel();
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/error-logs.txt', date("d-m-Y H:i:s") . "\n" . $e->getMessage());
        }
    }

    private function getClient(): Client {
        $client = new Client();
        $client->setAuthConfig(__DIR__ . '/google-config.json' );
        $client->addScope(Sheets::SPREADSHEETS);

        return $client;
    }

    public static function getDataExel(): array {
        $result = [];
        $file = self::getPathForXlsxFile();

        if (!file_exists($file)) {
            throw new Exception('файла с exel таблицей не существует!');
        }

        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();

        foreach($worksheet->toArray() as $row) {
            $row = array_filter($row);

            $result[] = array_values($row);
        }

        return $result;
    }

    private function getSpreadsheetId() {
        return get_field('spreadsheet_id', 'option');
    }

    private function clearExel(): void {
        $request = new ClearValuesRequest();
        $this->sheetsService->spreadsheets_values->clear($this->getSpreadsheetId(), self::RANGE, $request);
    }

    private function updateExel(): void {
        $body = new \Google_Service_Sheets_ValueRange([
            'values' => self::getDataExel()
        ]);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $this->sheetsService->spreadsheets_values->update($this->getSpreadsheetId(), self::RANGE, $body, $params);
    }

    public static function getPathForXlsxFile(): string {
        return sprintf('%s/googleApi/leftovers.xlsx', get_template_directory());
    }
}