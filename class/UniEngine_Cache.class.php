<?php

// For safety, if we don't have this function, we need to create one
if(!function_exists('apc_exists') AND UNIENGINE_HASAPC)
{
    function apc_exists($keys)
    {
        apc_fetch($keys, $result);
        return $result;
    }
}

class UniEngine_Cache
{
    private $APCCache_Key = false;

    public function __construct()
    {
        if(UNIENGINE_HASAPC)
        {
            $this->APCCache_Key = UNIENGINE_UNIID.'_APCCache';
        }
    }

    public function __get($name)
    {
        if(UNIENGINE_HASAPC)
        {
            return apc_fetch($this->APCCache_Key.'__'.$name);
        }
        return null;
    }

    public function __set($name, $value)
    {
        if(UNIENGINE_HASAPC)
        {
            return apc_store($this->APCCache_Key.'__'.$name, $value);
        }
        return false;
    }

    public function __isset($name)
    {
        if(UNIENGINE_HASAPC)
        {
            return apc_exists($this->APCCache_Key.'__'.$name);
        }
        return false;
    }

    public function __unset($name)
    {
        if(UNIENGINE_HASAPC)
        {
            return apc_delete($this->APCCache_Key.'__'.$name);
        }
        return false;
    }
}

?>
