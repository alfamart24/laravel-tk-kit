<?
namespace alfamart24\laravel_tk_kit;

use Illuminate\Support\ServiceProvider;


class KitServiceProvider extends ServiceProvider
{
    /**
     * Инициализация расширения
     *
     * @return void
     */
    public function boot()
    {
        //Указываем, что файлы из папки config должны быть опубликованы при установке
        $this->publishes([__DIR__ . '/../config/' => config_path() . '/']);
    }
}