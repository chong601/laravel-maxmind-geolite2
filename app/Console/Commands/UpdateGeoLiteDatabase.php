<?php

namespace App\Console\Commands;

use App\Exceptions\MissingMaxMindKeyException;
use App\Jobs\LoadMaxmindDataToDatabase;
use App\Models\GeoipAsn;
use App\Models\GeoipCity;
use Exception;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class UpdateGeoLiteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maxmind-geolite2:updatedb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queries the MaxMind repository and update if a new update is available';

    /**
     * The API key
     */
    private $maxMindKey;


    /**
     * The URL of the endpoint.
     */

    protected $maxMindUrl = 'https://download.maxmind.com/app/geoip_download'; //?edition_id=GeoLite2-ASN-CSV&license_key=YOUR_LICENSE_KEY&suffix=zip

    /**
     * Associative array that contains what to download.
     */
    protected $downloadList = [
        'GeoLite2-ASN-CSV' => [
            'edition_id' => 'GeoLite2-ASN-CSV',
            'suffix' => 'zip',
            'hash_suffix' => 'zip.sha256',
            'column_names' => ['network','autonomous_system_number','autonomous_system_organization'],
            'temp_folder_name' => 'GeoLite2-ASN-CSV_20220329',
            'file_to_process' => [
                'blocks' => [
                    ['file_name' => 'GeoLite2-ASN-Blocks-IPv4.csv', 'ip_type' => 4],
                    ['file_name' => 'GeoLite2-ASN-Blocks-IPv6.csv', 'ip_type' => 6]
                ]
            ],
            'class_name' => GeoipAsn::class
        ],
        'GeoLite2-City-CSV' => [
            'edition_id' => 'GeoLite2-City-CSV',
            'suffix' => 'zip',
            'hash_suffix' => 'zip.sha256',
            'column_names' => ['network','geoname_id','registered_country_geoname_id','represented_country_geoname_id','is_anonymous_proxy','is_satellite_provider','postal_code','latitude','longitude','accuracy_radius'],
            'temp_folder_name' => 'GeoLite2-City-CSV_20220329',
            'file_to_process' => [
                'blocks' => [
                    ['file_name' => 'GeoLite2-City-Blocks-IPv4.csv', 'ip_type' => 4],
                    ['file_name' => 'GeoLite2-City-Blocks-IPv6.csv', 'ip_type' => 6]
                ],
                // Disable locations first
                // 'locations' => [
                //     'GeoLite2-City-Locations-de.csv',
                //     'GeoLite2-City-Locations-en.csv',
                //     'GeoLite2-City-Locations-es.csv',
                //     'GeoLite2-City-Locations-fr.csv',
                //     'GeoLite2-City-Locations-ja.csv',
                //     'GeoLite2-City-Locations-pt-BR.csv',
                //     'GeoLite2-City-Locations-ru.csv',
                //     'GeoLite2-City-Locations-zh-CN.csv',
                // ]
            ],
            'class_name' => GeoipCity::class
        ],
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Fetch and check if the key is configured.
        // Throw an exception if the key is not specified
        $this->maxMindKey = config('maxmind.api_key');
        if ($this->maxMindKey === 'NO_KEY_SPECIFIED') {
            throw new MissingMaxMindKeyException('No key is specified. Please set MAXMIND_GEOIP_KEY in your .env!');
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Create the temp dir if doesn't exist yet
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0770, true);
        }

        $client = new Client();
        $error = [];
        foreach ($this->downloadList as $key => $item) {
        //     try {
        //         $hash = $client->request('get', $this->maxMindUrl, [
        //             RequestOptions::QUERY => ['edition_id' => $item['edition_id'], 'license_key' => $this->maxMindKey, 'suffix' => $item['hash_suffix']],
        //             RequestOptions::SINK => storage_path(sprintf('app/temp/%s.%s', $item['edition_id'], $item['suffix']))
        //         ]);
        //     } catch (ClientException $e) {
        //         $error[] = 'Unable to download hash for ' . $item['edition_id'] . ' due to ' . $e->getMessage();
        //         return 1;
        //     }

        //     try {
        //         $data = $client->request('get', $this->maxMindUrl, [
        //             RequestOptions::QUERY => ['edition_id' => $item['edition_id'], 'license_key' => $this->maxMindKey, 'suffix' => $item['suffix']],
        //             RequestOptions::SINK => storage_path(sprintf('app/temp/%s.%s', $item['edition_id'], $item['suffix']))
        //         ]);
        //     } catch (ClientException $e) {
        //         $error[] = 'Unable to download file for ' . $item['edition_id'] . ' due to ' . $e->getMessage();
        //         return 1;
        //     }

        //     $file_hash = hash_file('sha256', storage_path(sprintf('app/temp/%s.%s', $item['edition_id'], $item['suffix'])));
        //     $actual_hash = explode(' ',$hash->getBody()->getContents())[0];
            $file_hash = $actual_hash = 'a';
            if ($file_hash === $actual_hash) {
                // // handle file
                //print("Hash is valid: $file_hash matches the expected $actual_hash\n");
                // // unzip file
                // $archive = new ZipArchive;
                // $archive->open(storage_path(sprintf('app/temp/%s.%s', $item['edition_id'], $item['suffix'])));
                // $archive->extractTo(storage_path('app/temp'));
                foreach ($item['file_to_process'] as $category => $file_structure) {
                    foreach ($file_structure as $file) {
                        print(sprintf("Extracting file %s from %s category...\n", $file['file_name'], $category));
                        if (($file_handle = fopen(storage_path(sprintf('app/temp/%s/%s', $item['temp_folder_name'], $file['file_name'])), 'r')) !== false) {
                            $batch = [];
                            // cheap hack and skip the first line
                            fgetcsv($file_handle);
                            while (($data = fgetcsv($file_handle)) !== false) {
                                $batch[] = $this->proxyExtraction($data, $key, $file['ip_type']);
                                if (count($batch) === 5000) {
                                    LoadMaxmindDataToDatabase::dispatch($batch, $item['class_name']);
                                    $batch = null;
                                }
                            }
                            // dispatch one last time to move data to database
                            LoadMaxmindDataToDatabase::dispatch($batch, $item['class_name']);

                            fclose($file_handle);
                        }
                    }
                }
            } else {
                // hash failed, break loop
                // return 1;
            }
        }
        return 0;
    }

    /**
     * Adaptable class to handle mixed-data situations
     */
    private function proxyExtraction($data, $edition_id, $ip_type)
    {
        switch ($edition_id) {
            case 'GeoLite2-ASN-CSV':
                return $this->extractAsnData($data, $ip_type);
                break;
            case 'GeoLite2-City-CSV':
                return $this->extractCityData($data, $ip_type);
                break;
            default:
                throw new Exception("Unknown $edition_id ediition ID. Please raise a bug ticket!");
                break;
        }
    }

    /**
     * Handle ASN data
     */
    private function extractAsnData($data, $ip_type)
    {
        // It has no problem with the data. For now.
        $final_data = [];
        $columns = $this->downloadList['GeoLite2-ASN-CSV']['column_names'];
        foreach ($columns as $index => $columnName) {
            switch ($columnName) {
                case $columns[0]:
                    $final_data[$columnName] = $data[$index];
                    break;
                case $columns[1]:
                    $final_data[$columnName] = empty($data[$index]) ? -1 : intval($data[$index]);
                    break;
                case $columns[2]:
                    $final_data[$columnName] = empty($data[$index]) ? 'No Data' : $data[$index];
                    break;
            }
        }
        $final_data['ipType'] = $ip_type;
        return $final_data;
    }

    /**
     * Handle city data
     */
    private function extractCityData($data, $ip_type)
    {
        $final_data = [];
        $columns = $this->downloadList['GeoLite2-City-CSV']['column_names'];
        foreach ($columns as $index => $columnName) {
            switch ($columnName) {
                case $columns[0]:
                    $final_data[$columnName] = $data[$index];
                    break;
                case $columns[1]:
                    $final_data[$columnName] = empty($data[$index])? -1: intval($data[$index]) ;
                    break;
                case $columns[2]:
                    $final_data[$columnName] = empty($data[$index])? -1: intval($data[$index]) ;
                    break;
                case $columns[3]:
                    $final_data[$columnName] = empty($data[$index])? -1: intval($data[$index]) ;
                    break;
                case $columns[4]:
                    $final_data[$columnName] = empty($data[$index])? false: intval($data[$index]) ;
                    break;
                case $columns[5]:
                    $final_data[$columnName] = empty($data[$index])? false: intval($data[$index]) ;
                    break;
                case $columns[6]:
                    $final_data[$columnName] = empty($data[$index])? 'No Data': $data[$index] ;
                    break;
                case $columns[7]:
                    $final_data[$columnName] = empty($data[$index])? -1: floatval($data[$index]) ;
                    break;
                case $columns[8]:
                    $final_data[$columnName] = empty($data[$index])? -1: floatval($data[$index]) ;
                    break;
                case $columns[9]:
                    $final_data[$columnName] = empty($data[$index])? -1: intval($data[$index]) ;
                    break;
            }
        }
        $final_data['ipType'] = $ip_type;
        return $final_data;
    }
}
