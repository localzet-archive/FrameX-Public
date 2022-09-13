<?php

/**********************************************************
 *  @author Ivan Zorin <creator@localzet.ru>              *
 *  @license GNU General Public License v3.0              *
 *  @copyright Zorin Projects <www.localzet.ru>           *
 **********************************************************/

namespace LIS\Storage;

/**
 * Session storage manager
 */
class Session
{
    /**
     * Namespace
     *
     * @var string
     */
    protected $storeNamespace = 'STORAGE';

    /**
     * Key prefix
     *
     * @var string
     */
    protected $keyPrefix = '';


    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $key = $this->keyPrefix . strtolower($key);

        if (session()->exist($this->storeNamespace) && isset(session()->get($this->storeNamespace)[$key])) {
            $value = session()->get($this->storeNamespace)[$key];

            if (is_array($value) && array_key_exists('lateObject', $value)) {
                $value = unserialize($value['lateObject']);
            }

            return $value;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $key = $this->keyPrefix . strtolower($key);

        if (is_object($value)) {
            // We encapsulate as our classes may be defined after session is initialized.
            $value = ['lateObject' => serialize($value)];
        }

        $s = session()->get($this->storeNamespace);
        $s[$key] = $value;
        session()->put([$this->storeNamespace => $s]);
        session()->save();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        session()->delete($this->storeNamespace);
        session()->save();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $key = $this->keyPrefix . strtolower($key);

        if (session()->exist($this->storeNamespace) && isset(session()->get($this->storeNamespace)[$key])) {
            $tmp = session()->get($this->storeNamespace);

            unset($tmp[$key]);

            session()->put([$this->storeNamespace => $tmp]);
            session()->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMatch($key)
    {
        $key = $this->keyPrefix . strtolower($key);

        if (session()->exist($this->storeNamespace) && count(session()->get($this->storeNamespace))) {
            $tmp = session()->get($this->storeNamespace);

            foreach ($tmp as $k => $v) {
                if (strstr($k, $key)) {
                    unset($tmp[$k]);
                }
            }

            session()->put([$this->storeNamespace => $tmp]);
            session()->save();
        }
    }
}
