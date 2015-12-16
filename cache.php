<?php

namespace Zend\Stdlib {
use Traversable;
class Message implements MessageInterface
{
    /**
     * @var array
     */
    protected $metadata = array();

    /**
     * @var string
     */
    protected $content = '';

    /**
     * Set message metadata
     *
     * Non-destructive setting of message metadata; always adds to the metadata, never overwrites
     * the entire metadata container.
     *
     * @param  string|int|array|Traversable $spec
     * @param  mixed $value
     * @throws Exception\InvalidArgumentException
     * @return Message
     */
    public function setMetadata($spec, $value = null)
    {
        if (is_scalar($spec)) {
            $this->metadata[$spec] = $value;
            return $this;
        }
        if (!is_array($spec) && !$spec instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected a string, array, or Traversable argument in first position; received "%s"',
                (is_object($spec) ? get_class($spec) : gettype($spec))
            ));
        }
        foreach ($spec as $key => $value) {
            $this->metadata[$key] = $value;
        }
        return $this;
    }

    /**
     * Retrieve all metadata or a single metadatum as specified by key
     *
     * @param  null|string|int $key
     * @param  null|mixed $default
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function getMetadata($key = null, $default = null)
    {
        if (null === $key) {
            return $this->metadata;
        }

        if (!is_scalar($key)) {
            throw new Exception\InvalidArgumentException('Non-scalar argument provided for key');
        }

        if (array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }

        return $default;
    }

    /**
     * Set message content
     *
     * @param  mixed $value
     * @return Message
     */
    public function setContent($value)
    {
        $this->content = $value;
        return $this;
    }

    /**
     * Get message content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $request = '';
        foreach ($this->getMetadata() as $key => $value) {
            $request .= sprintf(
                "%s: %s\r\n",
                (string) $key,
                (string) $value
            );
        }
        $request .= "\r\n" . $this->getContent();
        return $request;
    }
}
}

namespace Zend\EventManager {
use Zend\Stdlib\CallbackHandler;
use Zend\Stdlib\PriorityQueue;
class GlobalEventManager
{
    /**
     * @var EventManagerInterface
     */
    protected static $events;

    /**
     * Set the event collection on which this will operate
     *
     * @param  null|EventManagerInterface $events
     * @return void
     */
    public static function setEventCollection(EventManagerInterface $events = null)
    {
        static::$events = $events;
    }

    /**
     * Get event collection on which this operates
     *
     * @return EventManagerInterface
     */
    public static function getEventCollection()
    {
        if (null === static::$events) {
            static::setEventCollection(new EventManager());
        }
        return static::$events;
    }

    /**
     * Trigger an event
     *
     * @param  string        $event
     * @param  object|string $context
     * @param  array|object  $argv
     * @param  null|callable $callback
     * @return ResponseCollection
     */
    public static function trigger($event, $context, $argv = array(), $callback = null)
    {
        return static::getEventCollection()->trigger($event, $context, $argv, $callback);
    }

    /**
     * Trigger listeners until return value of one causes a callback to evaluate
     * to true.
     *
     * @param  string $event
     * @param  string|object $context
     * @param  array|object $argv
     * @param  callable $callback
     * @return ResponseCollection
     * @deprecated Please use trigger()
     */
    public static function triggerUntil($event, $context, $argv, $callback)
    {
        trigger_error(
            'This method is deprecated and will be removed in the future. Please use trigger() instead.',
            E_USER_DEPRECATED
        );
        return static::trigger($event, $context, $argv, $callback);
    }

    /**
     * Attach a listener to an event
     *
     * @param  string $event
     * @param  callable $callback
     * @param  int $priority
     * @return CallbackHandler
     */
    public static function attach($event, $callback, $priority = 1)
    {
        return static::getEventCollection()->attach($event, $callback, $priority);
    }

    /**
     * Detach a callback from a listener
     *
     * @param  CallbackHandler $listener
     * @return bool
     */
    public static function detach(CallbackHandler $listener)
    {
        return static::getEventCollection()->detach($listener);
    }

    /**
     * Retrieve list of events this object manages
     *
     * @return array
     */
    public static function getEvents()
    {
        return static::getEventCollection()->getEvents();
    }

    /**
     * Retrieve all listeners for a given event
     *
     * @param  string $event
     * @return PriorityQueue|array
     */
    public static function getListeners($event)
    {
        return static::getEventCollection()->getListeners($event);
    }

    /**
     * Clear all listeners for a given event
     *
     * @param  string $event
     * @return void
     */
    public static function clearListeners($event)
    {
        static::getEventCollection()->clearListeners($event);
    }
}
}
