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
      return true;
    }

    /**
     * Reads the session data
     *
     * @param string $session_id
     * @return string
     */
    public function read($session_id)
    {
        try {
            if ($this->useTransactions) {
                // MySQL's default isolation, REPEATABLE READ, causes deadlock for different sessions.
                $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                $this->db->beginTransaction();
            } else {
                $this->unlockStatements[] = $this->getLock($session_id);
            }
            $sql = "SELECT $this->col_expiry, $this->col_data
                    FROM $this->table_sess WHERE $this->col_sid = :sid";
            // When using a transaction, SELECT FOR UPDATE is necessary
            // to avoid deadlock of connection that starts reading
            // before we write.
            if ($this->useTransactions) {
                $sql .= ' FOR UPDATE';
            }
            $selectStmt = $this->db->prepare($sql);
            $selectStmt->bindParam(':sid', $session_id);
            $selectStmt->execute();
            $results = $selectStmt->fetch(\PDO::FETCH_ASSOC);
            if ($results) {
                if ($results[$this->col_expiry] < time()) {
                    // Return an empty string if data out of date
                    return '';
                }
                return $results[$this->col_data];
            }
            // We'll get this far only if there are no results, which means
            // the session hasn't yet been registered in the database.
            if ($this->useTransactions) {
                $this->initializeRecord($selectStmt);
            }
            // Return an empty string if transactions aren't being used
            // and the session hasn't yet been registered in the database.
            return '';
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
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

    /**
 * Executes an application-level lock on the database
 *
 * @param $session_id
 * @return \PDOStatement Prepared statement to release the lock
 */
protected function getLock($session_id)
{
    $stmt = $this->db->prepare('SELECT GET_LOCK(:key, 50)');
    $stmt->bindValue(':key', $session_id);
    $stmt->execute();

    $releaseStmt = $this->db->prepare('DO RELEASE_LOCK(:key)');
    $releaseStmt->bindValue(':key', $session_id);

    return $releaseStmt;
}

/**
 * Registers new session ID in database when using transactions
 *
 * Exclusive-reading of non-existent rows does not block, so we need
 * to insert a row until the transaction is committed.
 *
 * @param \PDOStatement $selectStmt
 * @return string
 */
protected function initializeRecord(\PDOStatement $selectStmt)
{
    try {
        $sql = "INSERT INTO $this->table_sess ($this->col_sid, $this->col_expiry, $this->col_data)
                VALUES (:sid, :expiry, :data)";
        $insertStmt = $this->db->prepare($sql);
        $insertStmt->bindParam(':sid', $session_id);
        $insertStmt->bindParam(':expiry', $this->expiry, \PDO::PARAM_INT);
        $insertStmt->bindValue(':data', '');
        $insertStmt->execute();
        return '';
    } catch (\PDOException $e) {
        // Catch duplicate key error if the session has already been created.
        if (0 === strpos($e->getCode(), '23')) {
            // Retrieve existing session data written by the current connection.
            $selectStmt->execute();
            $results = $selectStmt->fetch(\PDO::FETCH_ASSOC);
            if ($results) {
                return $results[$this->col_data];
            }
            return '';
        }
        // Roll back transaction if the error was caused by something else.
        if ($this->db->inTransaction()) {
            $this->db->rollback();
        }
        throw $e;
    }
}

}