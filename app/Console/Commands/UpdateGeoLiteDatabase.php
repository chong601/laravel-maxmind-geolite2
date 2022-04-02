<?php

namespace App\Console\Commands;

use App\Exceptions\MissingMaxMindKeyException;
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
        [
            'edition_id' => 'GeoLite2-ASN-CSV',
            'suffix' => 'zip',
            'hash_suffix' => 'zip.sha256',
            'column_names' => ['network','autonomous_system_number','autonomous_system_organization']
        ],
        [
            'edition_id' => 'GeoLite2-City-CSV',
            'suffix' => 'zip',
            'hash_suffix' => 'zip.sha256',
            'column_names' => ['network','geoname_id','registered_country_geoname_id','represented_country_geoname_id','is_anonymous_proxy','is_satellite_provider','postal_code','latitude','longitude','accuracy_radius']
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
        foreach ($this->downloadList as $item) {
            try {
                $hash = $client->request('get', $this->maxMindUrl, [
                    RequestOptions::QUERY => ['edition_id' => $item['edition_id'], 'license_key' => $this->maxMindKey, 'suffix' => $item['hash_suffix']],
                    RequestOptions::SINK => storage_path(sprintf('app/temp/%s.%s', $item['edition_id'], $item['suffix']))
                ]);
            } catch (ClientException $e) {
                $error[] = 'Unable to download hash for ' . $item['edition_id'] . ' due to ' . $e->getMessage();
                return 1;
            }

            try {
                $data = $client->request('get', $this->maxMindUrl, [
                    RequestOptions::QUERY => ['edition_id' => $item['edition_id'], 'license_key' => $this->maxMindKey, 'suffix' => $item['suffix']],
                    RequestOptions::SINK => storage_path(sprintf('app/temp/%s.%s', $item['edition_id'], $item['suffix']))
                ]);
            } catch (ClientException $e) {
                $error[] = 'Unable to download file for ' . $item['edition_id'] . ' due to ' . $e->getMessage();
                return 1;
            }

            $file_hash = hash_file('sha256', storage_path(sprintf('app/temp/%s.%s', $item['edition_id'], $item['suffix'])));
            $actual_hash = explode(' ',$hash->getBody()->getContents())[0];
            if ($file_hash === $actual_hash) {
                // // handle file
                // print("Hash is valid: $file_hash matches the expected $actual_hash\n");
                // // unzip file
                // $archive = new ZipArchive;
                // $archive->open(storage_path(sprintf('app/temp/%s.%s', $item['edition_id'], $item['suffix'])));
                // $archive->extractTo(storage_path('app/temp'));

            } else {
                // hash failed, break loop
                // return 1;
            }
        }
        return 0;
    }
}
