<?php

namespace ILAB\MediaCloud\Utilities\Misc\Carbon\Laravel;

use ILAB\MediaCloud\Utilities\Misc\Carbon\Carbon;
use ILAB\MediaCloud\Utilities\Misc\Illuminate\Events\Dispatcher;
use ILAB\MediaCloud\Utilities\Misc\Illuminate\Events\EventDispatcher;
use ILAB\MediaCloud\Utilities\Misc\Illuminate\Translation\Translator as IlluminateTranslator;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Translator;
class ServiceProvider extends \ILAB\MediaCloud\Utilities\Misc\Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $service = $this;
        $events = $this->app['events'];
        if ($events instanceof \ILAB\MediaCloud\Utilities\Misc\Illuminate\Events\EventDispatcher || $events instanceof \ILAB\MediaCloud\Utilities\Misc\Illuminate\Events\Dispatcher) {
            $events->listen(\class_exists('ILAB\\MediaCloud\\Utilities\\Misc\\Illuminate\\Foundation\\Events\\LocaleUpdated') ? 'Illuminate\\Foundation\\Events\\LocaleUpdated' : 'locale.changed', function () use($service) {
                $service->updateLocale();
            });
            $service->updateLocale();
        }
    }
    public function updateLocale()
    {
        $translator = $this->app['translator'];
        if ($translator instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Translator || $translator instanceof \ILAB\MediaCloud\Utilities\Misc\Illuminate\Translation\Translator) {
            \ILAB\MediaCloud\Utilities\Misc\Carbon\Carbon::setLocale($translator->getLocale());
        }
    }
    public function register()
    {
        // Needed for Laravel < 5.3 compatibility
    }
}
