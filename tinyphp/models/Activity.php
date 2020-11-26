<?php
namespace models;


class Activity extends Model {
    
    protected static $table = 'activities';
    
    public $id;
    public $name;
    public $d1;
    public $d2;
    public $adcodes;
    
    const TYPES = [
        'NORMAL' => '普通活动'
    ];
    
    /**
     * 
     * {@inheritDoc}
     * @see \models\Model::validate()
     */
    function validate() {
    	
    }
}