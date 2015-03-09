<?php

// class namespace
namespace Admin\Auth;

use Zend\Authentication\Adapter\DbTable;
use Zend\Db\Sql;
use Zend\Db\Sql\Predicate\Operator as SqlOp;

class BcryptDbAdapter extends DbTable
{
    /**
     * _authenticateCreateSelect() - This method creates a Zend\Db\Sql\Select object that
     * is completely configured to be queried against the database.
     *
     * @return Sql\Select
     */
    protected function authenticateCreateSelect()
    {
        // get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->tableName)
            ->columns(array('*'))
			->where('admin_status','=',1)
            ->where(new SqlOp($this->identityColumn, '=', $this->identity));

        return $dbSelect;
    }

    /**
     * _authenticateQuerySelect() - This method accepts a Zend\Db\Sql\Select object and
     * performs a query against the database with that object.
     *
     * @param  Sql\Select $dbSelect
     * @throws Exception\RuntimeException when an invalid select object is encountered
     * @return array
     */
    protected function authenticateQuerySelect(Sql\Select $dbSelect)
    {
        $sql = new Sql\Sql($this->zendDb);
        $statement = $sql->prepareStatementForSqlObject($dbSelect);

        try {
            $result = $statement->execute();
            $resultIdentities = array();

            // create object ob Bcrypt class
            $bcrypt = new \Zend\Crypt\Password\Bcrypt();

            // iterate result, most cross platform way
            foreach ($result as $row) {
                if ($bcrypt->verify($this->credential, $row[$this->credentialColumn])) {
                    $row['zend_auth_credential_match'] = 1;
                    $resultIdentities[] = $row;
                }
            }

        } catch (\Exception $e) {
            throw new Exception\RuntimeException(
                'The supplied parameters to DbTable failed to '
                    . 'produce a valid sql statement, please check table and column names '
                    . 'for validity.', 0, $e
            );
        }

        return $resultIdentities;
    }
}
