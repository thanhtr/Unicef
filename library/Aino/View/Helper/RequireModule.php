<?php

class Aino_View_Helper_RequireModule extends Zend_View_Helper_Abstract
{
    private $modules = array();

    public function requireModule($modules = null)
    {
        if (is_array($modules)) {
            foreach ($modules as $module) {
                $this->_addModule($module);
            }
        } else if (is_string($modules)) {
            $this->_addModule($modules);
        }

        return $this;
    }

    private function _addModule($module)
    {
        if (is_string($module) && !(in_array($module, $this->modules))) {
            $this->modules[] = $module;
        }

        return $this;
    }

    public function __toString()
    {
        $modules = join("',\n            '", $this->modules);
        return <<<EOS
<script type="text/javascript">
        require([
            '{$modules}'
        ]);
    </script>
EOS;
    }
}