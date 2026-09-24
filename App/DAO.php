<?php

namespace App;

use FW\DB\Connection;

abstract class DAO extends Connection
{

    protected $conn;
    protected $sql;
    protected $resultado;
    protected $tabela;

    public abstract function create($obj);
    public abstract function delete($obj);
    public abstract function update($obj);
    public abstract function searchById($obj);
    public abstract function select();

    
}
