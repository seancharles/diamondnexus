<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace ForeverCompanies\StonesIntermediary\Setup;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;
class UpgradeData implements UpgradeDataInterface
{
    protected $resourceConnection;
    public function __construct(
        ResourceConnection $resourceConn
    ) {
        $this->resourceConnection = $resourceConn;
    }
    public function upgrade(
        ModuleDataSetupInterface  $setup,
        ModuleContextInterface  $context
    ) {
        $setup->startSetup();
        $query = "INSERT INTO stones_supplier(id, name, code, email, enabled)
            VALUES (1,'Blumoon','blumoon','sales.veebluemoon@gmail.com',1),
            (2,'Classic','classic','nitigya@classicgrowndiamonds.com, sales@classicgrowndiamonds.com',1),
            (3,'Greenrocks','greenrocks','LIATP@OM-DIAMONDS.COM',0),
            (4,'Internal','internal','fulfillment@forevercompanies.com, pd@forevercompanies.com',1),
            (5,'labrilliante','labrilliante','aaleksandrov@ndtcompany.com',0),
            (6,'Paradiam','paradiam','info@paradiam.org',1),
            (7,'Pdc','pdc','CS@newrockdiamonds.com, cs@newrockdiamonds.com, Deep@newrockdiamonds.com, LLin@pdcdiamonds.com',0),
            (8,'Stuller','stuller','Shaina_Smith@stuller.com, Aimee_segura@stuller.com, Blaine_latiolais@stuller.com',1),
            (9,'Washington','washington','sales@wdlabgrowndiamonds.com',1),
            (10,'diamondfoundry','diamondfoundry','adams@diamondfoundry.com, Adams@DiamondFoundry.com, lance@diamondfoundry.com, dffulfillment@diamondfoundry.com, tatiana@diamondfoundry.com',0),
            (11,'Meylor','meylor','strilka.all.projects@gmail.com, ykobzysta@yahoo.com',1),
            (12,'ethereal','ethereal','jparekh@etherealdiamond.com, bdm@etherealdiamond.com, sales@etherealdiamond.com',0),
            (13,'smilingrocks','smilingrocks','kish@smilingrocks.com, chirag@smilingrocks.com, Pathik@smilingrocks.com ',0),
            (14,'unique','unique','ulgd@forevercompanies.com, unique@diamondnexus.com, Nadia@usofny.com',0),
            (15,'qualitygold','qualitygold','jeffw@QGold.com, shah@qgold.com',1),
            (16,'flawlessallure','flawlessallure','shah.soham@flawlessallure.com',1),
            (17,'labs','labs','dana@labsdiamond.com',1),
            (18,'Fenix','Fenix','naman@fenixdiamonds.com, sales@fenixdiamonds.com',1),
            (19,'brilliantdiamonds','brilliantdiamonds','hiten@bdjinc.net',1),
            (20,'GrownDiamondCorpUSA','growndiamondcorpusa','gulrez@growndiamondcorp.com',0),
            (21,'InternationalDiamondJewelry','internationaldiamondjewelry','fulfillment@forevercompanies.com, pd@forevercompanies.com',0),
            (26,'ecogrown','ecogrown','Office@ecolgd.com, Sales@ecolgd.com',1),
            (27,'PureStones','purestones','riddhika@purestones.com, vijay@purestones.com',1),
            (28,'proudestlegendlimited','proudestlegendlimited','jparekh@etherealdiamond.com',0),
            (29,'dvjewelrycorporation','dvjewelrycorporation','jparekh@etherealdiamond.com',0),
            (31,'indiandiamonds','indiandiamonds','sales@indiandcorp.com',1),
            (32,'growndiamondcorp','growndiamondcorp','gulrez@growndiamondcorp.com',0),
            (33,'lushdiamonds','lushdiamonds','thelushdiamonds@gmail.com',0),
            (34,'ALTR','ALTR','orders@riamgroup.com',1);";
        $this->resourceConnection->getConnection()->query($query);
        $setup->endSetup();
    }
}