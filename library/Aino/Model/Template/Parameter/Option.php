<?php
namespace Aino\Model\Template\Parameter;
use Aino\Model;

class Option extends Model
{
    protected $idColumnName = 'value';

    protected $toArrayBlacklist = array('url');

    protected $label = '';

    protected $editable = true;

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setEditable($editable)
    {
        $this->editable = $editable;
        return $this;
    }

    public function getEditable()
    {
        return $this->editable;
    }
}