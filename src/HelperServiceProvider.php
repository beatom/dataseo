<?php


namespace Beatom\DataSeo;

use Illuminate\Support\ServiceProvider;


class HelperServiceProvider extends ServiceProvider
{


    public function register()
    {

        $this->publishes([
            __DIR__.'/../setting/dataseo.php' => base_path('config/dataseo.php'),
        ]);

        $this->publishes([
            __DIR__.'/../setting/2021_12_17_124303_create_log_data_seo.php'
            => base_path('database/migrations/2021_12_17_124303_create_log_data_seo.php'),
        ]);

        $this->publishes([
            __DIR__.'/../setting/DataSeoCommand.php' => base_path('app/Console/Commands/DataSeoCommand.php'),
        ]);
    }


}
