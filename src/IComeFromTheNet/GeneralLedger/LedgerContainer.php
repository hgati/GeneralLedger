<?php
namespace IComeFromTheNet\GeneralLedger;

use DateTime;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DBALGateway\Table\GatewayProxyCollection;
use DBALGateway\Metadata\Schema;
use IComeFromTheNet\GeneralLedger\Exception\LedgerException;
use IComeFromTheNet\GeneralLedger\Provider;

/**
 * This is a DI container for this library.
 * 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class LedgerContainer extends Container 
{

    protected $bIsRegistered = false;

    
    
    protected function getServiceProviders()
    {
        return [
             new Provider\TableMapProvider(),
             new Provider\DBGatewayProvider(),
             new Provider\TransactionProvider(),
        ];
        
    }
    
    
    /**
     * Class constructor
     * 
     * @param Symfony\Component\EventDispatcher\EventDispatcherInterface
     * @param Doctrine\DBAL\Connection $oAdapter
     * @param Psr\Log\LoggerInterface $oLogger
     * @param DBALGateway\Table\GatewayProxyCollection  $col
     */
    public function __construct(EventDispatcherInterface $oDispatcher, Connection $oAdapter, LoggerInterface $oLogger, GatewayProxyCollection $col)
    {
        $this['database']           = $oAdapter;
        $this['logger']             = $oLogger;
        $this['event']              = $oDispatcher;
        $this['gateway_collection'] = $col;
        
        $this->bIsRegistered        = false;
    }
    
    
    
    
    /**
     * Returns the database adapter
     * 
     * @return Doctrine\DBAL\Connection
     */ 
    public function getDatabaseAdapter()
    {
        return $this['database'];
    }
    
    
    
    /**
     * Returns the Applogger
     * 
     * @return Psr\Log\LoggerInterface
     */ 
    public function getAppLogger()
    {
        return $this['logger'];
    }
    
    /**
     * Returns the event dispatcher
     * 
     * @return Symfony\Component\EventDispatcher\EventDispatcherInterface
     */ 
    public function getEventDispatcher()
    {
        return $this['event'];
    }
    
    /**
     * Return the Gateway Collection
     * 
     * @return DBALGateway\Table\GatewayProxyCollection
     */ 
    public function getGatewayCollection()
    {
        return $this['gateway_collection'];
    }
    
    
    
    /**
     * Return the map of internal table names
     * to actual table names
     *  
     * @return array[internal => actual]
     */ 
    public function getTableMap()
    {
        return $this['table_map'];
    }
    
    
    /**
     * Return a shared transaction processor
     * 
     * @return TransactionProcessor
     */ 
    public function newTransactionProcessor()
    {
        return $this['transaction_processor'];
    }
    
    
   
    
    /**
     * Fetch the current datetime from the configured database
     * 
     * This not cache the result 
     * 
     * @return DateTime
     * 
     */ 
    public function getNow()
    {
        $oDatabase = $this->getDatabaseAdapter();
        $aTableMap = $this->getTableMap();

        return $oDatabase->fetchColumn('SELECT NOW() FROM  '.$aTableMap['ledger_transaction'],array(),0,array('datetime'));
    }
    
    
    /**
     * Starts the Service by registering the containers 
     * defined in self::getServiceProviders()
     * 
     * @return void
     */
    public function boot()
    {
        
        $oProviders = $this->getServiceProviders();
       
        foreach($oProviders as $oProvider){
            $this->register($oProvider);
        }
        
    }
    
    
    
}
/* End of Class */