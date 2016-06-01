<?php //-->
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace GoIP;

/**
 * Allows the ability to listen to events made known by another
 * piece of functionality. Events are items that transpire based
 * on an action. With events you can add extra functionality
 * right after the event has triggered.
 *
 * @package  Eden
 * @category Core
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Event extends Base
{
    /**
     * @var array $observers cache of event handlers
     */
    protected $observers = array();
    
    /**
     * Stops listening to an event
     *
     * @param string|null   $event    name of the event
     * @param callable|null $callable callback handler
     *
     * @return Eden\Core\Event
     */
    public function off($event = null, $callable = null)
    {
        //if there is no event and no callable
        if (is_null($event) && is_null($callable)) {
            //it means that they want to remove everything
            $this->observers = array();
            return $this;
        }

        $id = $this->getId($callable);

        //for each observer
        foreach ($this->observers as $i => $observer) {
            //if there is an event and is not being listened to
            if (!is_null($event) && $event != $observer[0]) {
                //skip it
                continue;
            }

            if (!is_null($callable) && $id != $observer[1]) {
                continue;
            }

            //unset it
            unset($this->observers[$i]);
        }

        return $this;
    }
     
    /**
     * Attaches an instance to be notified
     * when an event has been triggered
     *
     * @param *string   $event     the name of the event
     * @param *callable $callback  the event handler
     * @param bool      $important if true will be prepended in order
     *
     * @return Eden\Core\Event
     */
    public function on($event, $callable, $important = false)
    {
        $id = $this->getId($callable);

        //set up the observer
        $observer = array($event, $id, $callable);

        //if this is important
        if ($important) {
            //put the observer on the top of the list
            array_unshift($this->observers, $observer);
            return $this;
        }

        //add the observer
        $this->observers[] = $observer;
        return $this;
    }

    /**
     * Notify all observers of that a specific
     * event has happened
     *
     * @param string|null      $event the event to trigger
     * @param mixed[, mixed..] $arg   the arguments to pass to the handler
     *
     * @return Eden\Core\Event
     */
    public function trigger($event = null)
    {
        if (is_null($event)) {
            $trace = debug_backtrace();
            $event = $trace[1]['function'];
            if (isset($trace[1]['class']) && trim($trace[1]['class'])) {
                $event = str_replace('\\', '-', $trace[1]['class']).'-'.$event;
            }
        }
        
        //get the arguments
        $args = func_get_args();
        //shift out the event
        array_shift($args);

        //for each observer
        foreach ($this->observers as $observer) {
            //if this is the same event, call the method, if the method returns false
            if ($event == $observer[0] && call_user_func_array($observer[2], $args) === false) {
                //break out of the loop
                break;
            }
        }

        return $this;
    }

    /**
     * Tries to generate an ID for a callable.
     * We need to try in order to properly unlisten
     * to a variable
     *
     * @param *callable $callable the callback function
     *
     * @return string|false
     */
    protected function getId($callable)
    {
        if (is_array($callable)) {
            if (is_object($callable[0])) {
                $callable[0] = spl_object_hash($callable[0]);
            }

            return $callable[0].'::'.$callable[1];
        }

        if (is_string($callable)) {
            return $callable;
        }

        return false;
    }
}