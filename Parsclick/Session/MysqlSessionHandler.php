<?php

namespace Parsclick\Session;
/*
 * It's based on PDOSessionHandler in the
 * Symfony HttpFoundation component (https://github.com/symfony/
 * HttpFoundation/blob/master/Session/Storage/Handler/PdoSessionHandler.php).
 *
 * Copyright (c) 2004-2015 Fabien Potencier


namespace Parsclick\Sessions;

/**
 * Class MysqlSessionHandler
 * @package Parsclick\Sessions
 *
 * Custom session handler to store session data in MySQL/MariaDB
 */
class MysqlSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \PDO MySQL database connection
     */
    protected $db;

    /**
     * @var bool Determines whether to use transactions
     */
    protected $useTransactions;

    /**
     * @var int Unix timestamp indicating when session should expire
     */
    protected $expiry;

    /**
     * @var string Default table where session data is stored
     */
    protected $table_sess = 'sessions';

    /**
     * @var string Default column for session ID
     */
    protected $col_sid = 'sid';

    /**
     * @var string Default column for expiry timestamp
     */
    protected $col_expiry = 'expiry';

    /**
     * @var string Default column for session data
     */
    protected $col_data = 'data';

    /**
     * An array to support multiple reads before closing (manual, non-standard usage)
     *
     * @var array Array of statements to release application-level locks
     */
    protected $unlockStatements = [];

    /**
     * @var bool True when PHP has initiated garbage collection
     */
    protected $collectGarbage = false;

    /**
     * Constructor
     *
     * Requires a MySQL PDO database connection to the sessions table.
     * By default, the session handler uses transactions, which requires
     * the use of the InnoDB engine. If the sessions table uses the MyISAM
     * engine, set the optional second argument to false.
     *
     * @param \PDO $db MySQL PDO connection to sessions table
     * @param bool $useTransactions Determines whether to use transactions (default)
     */
    public function __construct(\PDO $db, $useTransactions = true)
    {
        $this->db = $db;
        if ($this->db->getAttribute(\PDO::ATTR_ERRMODE) !== \PDO::ERRMODE_EXCEPTION) {
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        $this->useTransactions = $useTransactions;
        $this->expiry = time() + (int) ini_get('session.gc_maxlifetime');
    }

    /**
     * Opens the session
     *
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name)
    {
      
    }

    /**
     * Reads the session data
     *
     * @param string $session_id
     * @return string
     */
    public function read($session_id)
    {

    }

    /**
     * Writes the session data to the database
     *
     * @param string $session_id
     * @param string $data
     * @return bool
     */
    public function write($session_id, $data)
    {

    }

    /**
     * Closes the session and writes the session data to the database
     *
     * @return bool
     */
    public function close()
    {

    }

    /**
     * Destroys the session
     *
     * @param int $session_id
     * @return bool
     */
    public function destroy($session_id)
    {

    }

    /**
     * Garbage collection
     *
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {

    }

}