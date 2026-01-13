<?php

declare(strict_types=1);

namespace App\Core\Domain\Contracts\Event;

use Hyperf\Event\Contract\ListenerInterface;

// se não fosse assim não ia :(

/**
 * Quebrando aqui o DDD.
 *
 * Camada interna conhecendo uma camada externa
 * Talves eu precise reescrever o listner do hyperf pra aceitar outros listeners
 * Não compatívels apenas com \Hyperf\Event\Contract\ListenerInterface;
 */
interface Subscriber extends ListenerInterface
{
}
