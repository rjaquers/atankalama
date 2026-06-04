<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
class EventDispatcher
{
    private static $listeners = [];

    public static function listen($event, $callback)
    {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }
        self::$listeners[$event][] = $callback;
    }

    public static function dispatch($event, $data = null)
    {
        if (empty(self::$listeners[$event])) return;

        foreach (self::$listeners[$event] as $listener) {
            call_user_func($listener, $data);
        }
    }
}
