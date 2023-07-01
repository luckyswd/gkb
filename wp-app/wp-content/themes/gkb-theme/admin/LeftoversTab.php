<?php

namespace admin;

use Google\Exception;
use googleApi\GoogleClient;
use helpers\Helpers;

class LeftoversTab
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('rest_api_init', [$this, 'updateLeftovers']);
        add_action('rest_api_init', [$this, 'updateLeftoversGoogleApi']);
    }

    public function addMenu() {
        add_menu_page(
            'Остатки',
            'Остатки',
            'manage_options',
            'leftovers34',
            [$this, 'leftoversTableRender'],
            'dashicons-admin-generic',
            5
        );
    }

    public function leftoversTableRender(): void {
        $uploadFile = $this->getUploadFile();

        try {
            $rows = GoogleClient::getDataExel();
        } catch (Exception $e) {
            $message = $e->getMessage();

            echo "
                <div class='wrapper-leftovers'>
                 <h1 style='font-size: 32px; font-weight: 700'>$message</h1>
                 $uploadFile
                </div>
            ";

            return;
        }
        $thead = $this->getThead($rows);
        $tbody = $this->getTbody($rows);


        echo "<div class='wrap'>
            <div class='wrapper-leftovers'>
                <h1 style='font-size: 32px; font-weight: 700'>Остатки</h1>
                $uploadFile
            </div>
            <table class='wp-list-table widefat striped'>
                <thead>$thead</thead>
                <tbody>$tbody</tbody>
            </table>
        </div>";
    }

    private function getThead(
        array $rows,
    ): string {
        $thead = '<tr>';

        foreach ($rows[0] as $theadRow) {
            $thead .= "<th style='font-size: 22px'>$theadRow</th>";
        }

        $thead .= '</tr>';

        return $thead;
    }

    private function getTbody(
        array $rows,
    ): string {
        $tbody = '';
        foreach ($rows as $key => $row) {
            if ($key === 0) {
                continue;
            }

            $tbody .= '<tr>';

            foreach ($row as $value) {
                $tbody .= "<td>$value</td>";
            }

            $tbody .= '</tr>';
        }

        return $tbody;
    }

    private function getUploadFile(): string {
        $ajaxUrl = sprintf('%s/wp-json/admin/v2/update-leftovers', home_url());
        $urlUpdateGoogleApi = sprintf('%s/wp-json/admin/v2/update-leftovers-google-api', home_url());

        return "<label>
                    <div class='btn-leftovers'>Загрузить остатки</div>
                    <input 
                    data-url='$ajaxUrl'
                    data-url-google-api='$urlUpdateGoogleApi'
                    class='upload-file'
                    type='file' 
                    name='filename' 
                    accept='.xlsx, .xls, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel'
                    >
                </label>";
    }

    public function updateLeftovers(): void {
        register_rest_route('admin/v2', '/update-leftovers', [
            'methods' => 'POST',
            'callback' => function () {
                $request = Helpers::getRequest(Helpers::METHOD_POST, $_FILES);
                $dataFile = $request->get_param('file');

                if (move_uploaded_file($dataFile['tmp_name'], GoogleClient::getPathForXlsxFile())) {
                    $args = [
                        'method' => 'GET',
                    ];

                    wp_send_json([
                        'status' => true,
                    ]);

                    wp_die();
                }

                wp_send_json([
                    'status' => false,
                ]);

                wp_die();
            },
        ]);
    }

    public function updateLeftoversGoogleApi(): void {
        register_rest_route('admin/v2', '/update-leftovers-google-api', [
            'methods' => 'POST',
            'callback' => function () {
                $googleApi = new \googleApi\GoogleClient();
                $googleApi->init();

                wp_die();
            },
        ]);
    }
}