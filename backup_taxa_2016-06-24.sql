-- MySQL dump 10.13  Distrib 5.7.12, for Linux (i686)
--
-- Host: localhost    Database: plantboo_main
-- ------------------------------------------------------
-- Server version	5.7.12-0ubuntu1.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `taxa_kind`
--

DROP TABLE IF EXISTS `taxa_kind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxa_kind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ord` int(11) DEFAULT NULL,
  `initials` varchar(5) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `order` (`ord`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='Taxa kind';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_kind`
--

LOCK TABLES `taxa_kind` WRITE;
/*!40000 ALTER TABLE `taxa_kind` DISABLE KEYS */;
INSERT INTO `taxa_kind` VALUES (1,NULL,NULL,'Radice',NULL),(2,1,'Tav','Tavola',''),(3,2,'Fam.','Famiglia',''),(4,3,'Sp','Specie','');
/*!40000 ALTER TABLE `taxa_kind` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `region`
--

DROP TABLE IF EXISTS `region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `region` (
  `id` varchar(20) NOT NULL COMMENT 'Region id',
  `name` varchar(100) DEFAULT NULL COMMENT 'Region description',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Region description';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `region`
--

LOCK TABLES `region` WRITE;
/*!40000 ALTER TABLE `region` DISABLE KEYS */;
INSERT INTO `region` VALUES ('01','Piemonte'),('02','Valle d\'Aosta/Vallée d\'Aoste'),('03','Lombardia'),('04','Trentino-Alto Adige/Südtirol'),('05','Veneto'),('06','Friuli-Venezia Giulia'),('07','Liguria'),('08','Emilia Romagna'),('09','Toscana'),('10','Umbria'),('11','Marche'),('12','Lazio'),('13','Abruzzo'),('14','Molise'),('15','Campania'),('16','Puglia'),('17','Basilicata'),('18','Calabria'),('19','Sicilia'),('20','Sardegna');
/*!40000 ALTER TABLE `region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxa`
--

DROP TABLE IF EXISTS `taxa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxa` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id of taxonomy',
  `taxa_kind_id` int(11) DEFAULT NULL COMMENT 'Taxonomy kind',
  `name` varchar(100) DEFAULT NULL COMMENT 'Taxonomy name',
  `description` text COMMENT 'Taxonomi description',
  `creation_datetime` datetime DEFAULT NULL COMMENT 'Creation datetime',
  `change_datetime` datetime DEFAULT NULL COMMENT 'Last change datetime',
  `col_id` varchar(40) DEFAULT NULL COMMENT 'Id in Catalog of life database',
  `eol_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Id of taxa in Enciclopedia of life',
  `is_list` smallint(1) NOT NULL COMMENT 'Is list',
  PRIMARY KEY (`id`),
  KEY `fk_taxonomy_kind_idx` (`taxa_kind_id`),
  KEY `modify_datetime` (`change_datetime`)
) ENGINE=MyISAM AUTO_INCREMENT=104 DEFAULT CHARSET=utf8 COMMENT='Taxa';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa`
--

LOCK TABLES `taxa` WRITE;
/*!40000 ALTER TABLE `taxa` DISABLE KEYS */;
INSERT INTO `taxa` VALUES (103,4,'Equisetum ramosissimus','','2016-06-22 17:26:47','2016-06-22 17:26:48','',NULL,0),(102,4,'Equisetum palustre','','2016-06-22 17:26:24','2016-06-22 17:26:24','',NULL,0),(101,4,'Equisetum arvense','Pianta con lunghi e invadenti rizomi, ingrossati talvolta in tuberi. I fusti fertili vengono prodotti in primavera.<br /><br />Possono presentare la parte fertile (strobilo) all\'apice di un fusto ramificato, a differenza dei precedenti, diverse specie, tra cui ricordiamo le seguenti: <em>E. palustre</em> L. equiseto di palude, è<br />comune nei fossi, nei luoghi umidi fino al piano alpino in quasi tutta Europa: è specie perenne, con fusto di 30-60 cm verde chiaro, rami eretti e guaine alla base dei rami verde brillante; <em>E. ramosissimum</em> Desf. presenta fusti molto deboli, verdi e macchiati di bianco, con i denti delle guaine scuri; in particolare le parti fertili (strobili) sono piccole, brune; la pianta è molto ramificata, con rami più o meno abbondanti, gracili ed eretti; è specie comune sia nei luoghi umidi che in quelli aridi sassosi, del centro e nord Europa. Entrambe queste specie sporificano da maggio ad agosto.','2016-06-22 17:25:10','2016-06-22 17:25:10','ed2d5cda0ca8178acafb14d79f4ceca4',597765,0),(100,4,'Equisetum telmateja','Pianta con lunghi rizomi sotterranei, che produce fusti fertili in primavera aiti 20 cm, con spighe allungate fino a 5 cm; i fusti sterili sono costolosi, con ramificazioni fitte verticillate.<br /><br />I fusti secchi di equiseto, per l\'alto contenuto in silice, si utilizzano nell\'agricoltura biodinamica. Tutti gli equiseti venivano usati in medicina come diuretici.','2016-06-22 17:21:19','2016-06-22 17:21:20','b66368c9f062c54a6f4dd9ec9a47813d',NULL,0),(99,3,'Graminaceae - Chiave 7','','2016-06-22 13:44:03','2016-06-22 13:44:03','',NULL,0),(98,3,'Graminaceae - Chiave 4-5-6','','2016-06-22 13:43:24','2016-06-22 13:43:24','',NULL,0),(97,3,'Graminaceae - Chiave 3','','2016-06-22 13:42:47','2016-06-22 13:42:47','',NULL,0),(96,3,'Graminaceae - Chiave 2','','2016-06-22 13:42:05','2016-06-22 13:42:05','',NULL,0),(95,3,'Graminaceae - Chiave 1','','2016-06-22 13:41:30','2016-06-22 13:41:31','',NULL,0),(94,3,'Juncaceae','','2016-06-22 13:40:50','2016-06-22 13:40:51','',NULL,0),(93,3,'Cyperaceae','','2016-06-22 13:40:21','2016-06-22 13:40:21','',NULL,0),(92,3,'Equisetaceae','Piante con fusto semplice o ramoso, articolato e costolato negli internodi; sono presenti rizomi. Le foglie, piccolissime, sono disposte a formare una guaina intorno al fusti. Sono piante senza fiori (crittogame vascolari) ohe presentano fusti fertili a clava che portano gli sporangi riuniti in spiga.','2016-06-22 13:35:24','2016-06-22 16:46:41','10802452a360d0fd47f032afb13951c7',4080,0),(91,3,'Ophioglossaceae','','2016-06-22 13:34:48','2016-06-22 13:34:48','',NULL,0),(90,3,'Plantaginaceae','','2016-06-22 13:32:36','2016-06-22 13:32:37','',NULL,0),(89,3,'Cannabinaceae','','2016-06-22 13:27:10','2016-06-22 13:27:10','',NULL,0),(88,3,'Polypodiaceae','','2016-06-22 13:22:03','2016-06-22 13:22:05','',NULL,0),(87,3,'Urticaceae','','2016-06-22 13:16:28','2016-06-22 13:16:28','',NULL,0),(86,3,'Amaranthaceae','','2016-06-22 13:06:58','2016-06-22 13:06:58','',NULL,0),(85,3,'Poligonaceae','','2016-06-22 13:05:52','2016-06-22 13:05:52','',NULL,0),(84,3,'Euphorbiaceae','','2016-06-22 13:04:51','2016-06-22 13:04:51','',NULL,0),(83,3,'Chenopodiaceae','','2016-06-22 12:46:42','2016-06-22 12:46:42','',NULL,0),(82,3,'Ericaceae','','2016-06-22 12:45:57','2016-06-22 12:45:57','',NULL,0),(81,3,'Orobancaceae','','2016-06-22 12:38:56','2016-06-22 12:38:56','',NULL,0),(80,3,'Portolacaceae','','2016-06-22 12:38:10','2016-06-22 12:38:10','',NULL,0),(79,3,'Crassulaceae','','2016-06-22 12:37:29','2016-06-22 12:37:29','',NULL,0),(78,3,'Lentibulariaceae','','2016-06-22 12:36:56','2016-06-22 12:36:56','',NULL,0),(77,3,'Orchidaceae','','2016-06-22 12:35:35','2016-06-22 12:35:36','',NULL,0),(76,3,'Commellinaceae','','2016-06-22 12:34:33','2016-06-22 12:34:34','',NULL,0),(75,3,'Iridaceae','','2016-06-22 12:33:50','2016-06-22 12:33:50','',NULL,0),(74,3,'Amaryllidaceae','','2016-06-22 12:33:19','2016-06-22 12:33:19','',NULL,0),(73,3,'Liliaceae','','2016-06-22 13:00:49','2016-06-22 13:00:50','',NULL,0),(72,3,'Santalaceae','','2016-06-22 12:59:05','2016-06-22 12:59:05','',NULL,0),(71,3,'Araceae','','2016-06-22 12:57:29','2016-06-22 12:57:29','',NULL,0),(70,3,'Arisolochiaceae','','2016-06-22 12:56:55','2016-06-22 12:56:55','',NULL,0),(69,3,'Cucurbitaceae','','2016-06-22 12:47:25','2016-06-22 12:47:25','',NULL,0),(67,3,'Hydrophyllaceae','','2016-06-22 12:44:01','2016-06-22 12:44:01','',NULL,0),(66,3,'Verbenaceae','','2016-06-22 12:42:50','2016-06-22 12:42:50','',NULL,0),(65,3,'Caprifogliaceae','','2016-06-22 12:41:50','2016-06-22 12:41:50','',NULL,0),(64,3,'Scrophulariaceae','','2016-06-22 12:33:07','2016-06-22 12:33:07','',NULL,0),(63,3,'Labiateae','','2016-06-22 12:31:53','2016-06-22 12:31:53','',NULL,0),(62,3,'Rubiaceae','','2016-06-22 12:27:15','2016-06-22 12:27:16','',NULL,0),(61,3,'Gentianaceae','','2016-06-22 12:25:26','2016-06-22 12:25:26','',NULL,0),(60,3,'Campanulaceae','','2016-06-22 12:24:31','2016-06-22 12:24:31','',NULL,0),(59,3,'Asclepadiaceae','','2016-06-22 12:23:56','2016-06-22 12:23:57','',NULL,0),(58,3,'Valerianaceae','','2016-06-22 12:22:15','2016-06-22 12:22:15','',NULL,0),(57,3,'Primulaceae','','2016-06-22 12:21:46','2016-06-22 12:21:46','',NULL,0),(55,3,'Pytholaccaceae','','2016-06-22 12:19:51','2016-06-22 12:19:51','',NULL,0),(54,3,'Convolvulaceae','','2016-06-22 12:19:02','2016-06-22 12:19:02','',NULL,0),(53,3,'Solanaceae','','2016-06-22 12:18:18','2016-06-22 12:18:19','',NULL,0),(52,3,'Boraginaceae','','2016-06-22 12:17:21','2016-06-22 12:17:22','',NULL,0),(51,3,'Dipsacaceae','','2016-06-21 17:55:53','2016-06-21 17:55:53','',NULL,0),(50,3,'Compositae','','2016-06-21 17:54:46','2016-06-21 17:54:46','',NULL,0),(49,3,'Globulariaceae','','2016-06-21 17:54:00','2016-06-21 17:54:00','',NULL,0),(48,3,'Oxalidaceae','','2016-06-21 17:51:13','2016-06-21 17:51:14','',NULL,0),(47,3,'Malvaceae','','2016-06-21 17:49:59','2016-06-21 17:49:59','',NULL,0),(46,3,'Saxifragaceae','','2016-06-21 17:46:03','2016-06-21 17:46:03','',NULL,0),(45,3,'Umbrellifereae','','2016-06-21 17:25:31','2016-06-21 17:25:32','',NULL,0),(44,3,'Resedaceae','','2016-06-21 17:24:22','2016-06-21 17:24:22','',NULL,0),(43,3,'Rutaceae','','2016-06-21 17:23:04','2016-06-21 17:23:04','',NULL,0),(42,3,'Ranuncolaceae','','2016-06-21 17:22:09','2016-06-21 17:22:09','',NULL,0),(41,3,'Rosaceae','','2016-06-21 17:20:45','2016-06-21 17:20:45','',NULL,0),(40,3,'Geraniaceae','','2016-06-21 17:18:32','2016-06-21 17:18:32','',NULL,0),(39,3,'Zygophillaceae','','2016-06-21 17:17:33','2016-06-21 17:17:34','',NULL,0),(38,3,'Papaveraceae','','2016-06-21 17:12:28','2016-06-21 17:12:28','',NULL,0),(37,3,'Polygalaceae','','2016-06-21 17:10:43','2016-06-21 17:10:43','',NULL,0),(36,3,'Leguminosae','','2016-06-21 17:10:10','2016-06-21 17:11:02','',NULL,0),(35,3,'Balsaminaceae','','2016-06-21 17:09:47','2016-06-21 17:09:48','',NULL,0),(34,3,'Violaceae','','2016-06-21 17:02:41','2016-06-21 17:02:42','',NULL,0),(33,3,'Crucifere','','2016-03-25 14:46:25','2016-03-25 14:46:25','',NULL,0),(32,3,'Onagraceae','','2016-03-25 14:46:06','2016-03-25 14:46:06','',NULL,0),(31,3,'Linaceae','','2016-03-25 14:37:53','2016-03-25 14:37:53','',NULL,0),(30,3,'Caryophyllaceae','','2016-03-25 14:37:06','2016-03-25 14:37:06','',NULL,0),(29,3,'Cistaceae','','2016-03-25 14:34:12','2016-03-25 14:34:13','',NULL,0),(28,3,'Hypericaceae','','2016-03-25 14:33:38','2016-03-25 14:33:38','',NULL,0),(27,3,'Lythraceae','','2016-03-25 14:31:44','2016-03-25 14:32:30','',NULL,0),(26,2,'23 - Piante con fiori poco appariscenti - Foglie a nervature pennate o palmate - Foglie palmate','','2016-03-25 14:00:49','2016-06-22 13:25:17','',NULL,1),(25,2,'22  - Piante con fiori poco appariscenti - Foglie a nervature pennate o palmate - Foglie lobate','','2016-03-25 14:00:26','2016-06-22 13:19:25','',NULL,1),(24,2,'21 -  Piante con fiori poco appariscenti - Foglie a nervature pennate o palmate - Foglie lanceolate','','2016-03-25 13:58:47','2016-06-22 13:14:50','',NULL,1),(23,2,'20 - Piante con fiori poco appariscenti - Foglie a nervature pennate o palmate - Foglie astate','','2016-03-25 13:57:59','2016-06-22 13:19:53','',NULL,1),(22,2,'25 - Piante con fiori poco appariscenti - Foglie a nervature parallele - Piante graminiformi','','2016-03-25 13:55:15','2016-06-22 13:39:40','',NULL,1),(21,2,'24 - Piante con fiori poco appariscenti - Foglie a nervature parallele - Piante non graminiformi','','2016-03-25 13:54:33','2016-06-22 13:31:26','',NULL,1),(20,2,'17 - Foglie a nervature parallele','','2016-03-25 13:03:42','2016-06-22 12:49:49','',NULL,1),(19,2,'16 - Foglie a nervature parallele','','2016-03-25 13:01:29','2016-06-22 12:49:36','',NULL,1),(18,2,'18 - Piante con foglie carnose o senza clorofilla','','2016-03-25 12:57:05','2016-06-22 12:49:00','',NULL,1),(17,2,'19 - Piante con foglie aghiformi o a squama','','2016-03-25 12:56:31','2016-06-22 12:48:35','',NULL,1),(16,2,'15 - Petali uniti - Fiore appariscente - Foglie palmate, cuoriformi, palmato-partite, sette o lacini','','2016-03-25 12:51:27','2016-06-22 12:56:09','',NULL,1),(15,2,'14 - Petali uniti - Fiore appariscente - Foglie palmate, cuoriformi, palmato-partite, sette o lacini','','2016-03-25 12:49:39','2016-06-22 12:55:56','',NULL,1),(14,2,'13 - Petali uniti - Fiore appariscente - Foglie pennato-partite, sette o laciniate','','2016-03-25 12:48:21','2016-06-22 12:55:26','',NULL,1),(13,2,'12 - Petali uniti - Fiore appariscente - Foglie pennato-partite, sette o laciniate','','2016-03-25 12:47:14','2016-06-22 12:55:15','',NULL,1),(12,2,'11 - Petali uniti - Fiore appariscente - Foglie ovali, elittiche o lanceolate','','2016-03-25 12:45:28','2016-06-22 12:54:00','',NULL,1),(11,2,'10 - Petali uniti - Fiore appariscente - Foglie ovali, elittiche o lanceolate','','2016-03-25 12:43:31','2016-06-22 12:53:10','',NULL,1),(10,2,'9  - Petali uniti - Fiore appariscente - Foglie ovali, elittiche o lanceolate','','2016-03-25 12:42:58','2016-06-22 12:52:54','',NULL,1),(9,2,'8 - Petali uniti - Fiore appariscente - Foglie ovali, elittiche o lanceolate','','2016-03-25 12:39:58','2016-06-22 12:52:42','',NULL,1),(8,2,'7 - Petali liberi - Fiore appariscente - Foglie palmate','','2016-03-25 12:34:48','2016-06-22 12:59:07','',NULL,1),(7,2,'6 - Petali liberi - Fiore appariscente - Foglie palmate','','2016-03-25 12:34:20','2016-06-22 12:59:20','',NULL,1),(6,2,'5 - Petali liberi - Fiore appariscente - Foglie palmate','','2016-03-25 12:30:29','2016-06-22 12:59:31','',NULL,1),(5,2,'4 - Petali liberi - Fiore appariscente - Foglie pennato-partite, sette o laciniate','','2016-03-25 12:29:49','2016-06-22 12:59:56','',NULL,1),(4,2,'3 - Petali liberi - Fiore appariscente - Foglie pennato-partite, sette o laciniate','','2016-03-25 12:27:04','2016-06-22 13:00:08','',NULL,1),(3,2,'2 - Petali liberi - Fiore appariscente - Foglie ovali, elittiche o lanceolate','','2016-03-25 12:25:15','2016-06-22 13:00:32','',NULL,1),(2,2,'1 - Petali liberi - Fiore appariscente - Foglie ovali, elittiche o lanceolate','','2016-03-25 12:24:51','2016-06-22 13:00:44','',NULL,1),(1,1,'Radice','Radice',NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `taxa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxa_region`
--

DROP TABLE IF EXISTS `taxa_region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxa_region` (
  `taxa_id` int(11) NOT NULL,
  `region_id` varchar(20) NOT NULL,
  PRIMARY KEY (`taxa_id`,`region_id`),
  KEY `fk_taxa_region_region_idx` (`region_id`),
  KEY `fk_taxa_region_taxa_idx` (`taxa_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Association between taxa and region';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_region`
--

LOCK TABLES `taxa_region` WRITE;
/*!40000 ALTER TABLE `taxa_region` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxa_region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxa_attribute`
--

DROP TABLE IF EXISTS `taxa_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxa_attribute` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id of taxa attribute',
  `name` varchar(100) NOT NULL COMMENT 'Taxa attribute name',
  `description` text COMMENT 'Taxa attribute desciption',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='Taxa attribute';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_attribute`
--

LOCK TABLES `taxa_attribute` WRITE;
/*!40000 ALTER TABLE `taxa_attribute` DISABLE KEYS */;
INSERT INTO `taxa_attribute` VALUES (1,'Sinonimi',NULL),(2,'Autore',NULL),(3,'Etimologia',NULL),(4,'Tipo di vegetazione',NULL),(5,'Diffusione geografica',NULL),(6,'Nome italiano',NULL);
/*!40000 ALTER TABLE `taxa_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxa_attribute_value`
--

DROP TABLE IF EXISTS `taxa_attribute_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxa_attribute_value` (
  `taxa_id` int(11) NOT NULL COMMENT 'Id of taxa',
  `taxa_attribute_id` int(11) NOT NULL COMMENT 'Id of taxa attribute',
  `value` varchar(100) DEFAULT NULL COMMENT 'Value',
  PRIMARY KEY (`taxa_id`,`taxa_attribute_id`),
  KEY `fk_taxa_attribute_value_taxa_id` (`taxa_id`),
  KEY `fk_taxa_attribute_value_taxa_attribute` (`taxa_attribute_id`),
  KEY `value` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Taxa attribute value';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_attribute_value`
--

LOCK TABLES `taxa_attribute_value` WRITE;
/*!40000 ALTER TABLE `taxa_attribute_value` DISABLE KEYS */;
INSERT INTO `taxa_attribute_value` VALUES (100,1,'(= E. maximum Duval-Jouv.)'),(100,2,'Ehrh.'),(100,3,'Il nome del genere deriva dal latino e significa \"crine di cavallo\", per l\'aspetto della pianta; tél'),(100,4,'Comune e diffusa nei fossi, negli ambienti umidi, al margine di prati e campi fino alla zona subalpi'),(100,5,'Specie comunissima In tutta Europa, fuorché nelle estreme zone settentrionali.'),(101,2,'Linneo'),(101,6,'Equiseto dei campi'),(101,4,'Comune nei luoghi umidi e nei campi, nei terreni sabbiosi e argillosi fino a 2000 m; è specie infest'),(101,5,'Diffusissima in tutta Europa.');
/*!40000 ALTER TABLE `taxa_attribute_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxa_image`
--

DROP TABLE IF EXISTS `taxa_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxa_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id of taxa image',
  `taxa_id` int(11) DEFAULT NULL COMMENT 'Id of taxa',
  `filename` varchar(200) DEFAULT NULL COMMENT 'Filename',
  PRIMARY KEY (`id`),
  KEY `fk_taxa_image_1_idx` (`taxa_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='Taxa images';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_image`
--

LOCK TABLES `taxa_image` WRITE;
/*!40000 ALTER TABLE `taxa_image` DISABLE KEYS */;
INSERT INTO `taxa_image` VALUES (7,100,'/00/00/07.png'),(8,101,'/00/00/08.png');
/*!40000 ALTER TABLE `taxa_image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dico_item`
--

DROP TABLE IF EXISTS `dico_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dico_item` (
  `parent_taxa_id` int(11) NOT NULL,
  `id` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `taxa_id` int(11) DEFAULT NULL,
  `photo_id` int(11) NOT NULL COMMENT 'Photo id',
  PRIMARY KEY (`id`,`parent_taxa_id`),
  KEY `fk_dico_item_taxa_idx` (`taxa_id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Dicotomy item';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dico_item`
--

LOCK TABLES `dico_item` WRITE;
/*!40000 ALTER TABLE `dico_item` DISABLE KEYS */;
INSERT INTO `dico_item` VALUES (1,'0','Piante con fiore appariscente e foglie verdi, laminari e non carnose',NULL,0),(1,'1','Piante con fiori poco appariscenti, riuniti di solito in infiorescenze, spesso verdi; fogli verdi laminari non carnose',NULL,0),(1,'00','Foglie a nervature pennate o palmate',NULL,0),(1,'01','Foglie a nervature parallele, lineari o lanceolate',NULL,0),(1,'000','Petali liberi',NULL,0),(1,'001','Petali uniti',NULL,0),(1,'0000','Foglie ovali, elittiche o lanceolate',NULL,0),(1,'0001','Foglie non intere',NULL,0),(1,'00000','Corolle raggiate',NULL,0),(1,'00010','Foglie pennate, partite, sette o laciniate',NULL,0),(1,'00011','Foglie palmate',NULL,0),(1,'00001','Corolle non raggiate (zigomorfe)',3,0),(1,'000000','Foglie opposte',2,0),(1,'000001','Foglie alterne',NULL,0),(1,'0000010','Petali 4',2,0),(1,'0000011','Petali 5-6',3,0),(1,'000100','Petali 4',NULL,0),(1,'000101','Petali 5',NULL,0),(1,'0001000','Corolle raggiate',4,0),(1,'0001001','Corolle non raggiate (zigomorfe)',4,0),(1,'0001010','Corolle raggiate',NULL,0),(1,'0001011','Corolle non raggiate (zigomorfe)',6,0),(1,'00010100','Foglie opposte',4,0),(1,'00010101','Foglie alterne',NULL,0),(1,'000101010','Infiorescenza non a ombrella',5,0),(1,'000101011','Infiorescenza a ombrella',5,0),(1,'000110','Foglie cuoriformi o rotonde',NULL,0),(1,'000111','Foglie palmato-partite, sette o laciniate',NULL,0),(1,'0001100','Corille non raggiate (zigomorfe)',6,0),(1,'0001101','Corolle raggiate',6,0),(1,'0001110','Corolle raggiate',NULL,0),(1,'0001111','Corolle non raggiate (zigomorfe)',8,0),(1,'00011100','Foglie singole',NULL,0),(1,'00011101','Foglie trifogliate',8,0),(1,'000111000','Foglie opposte',7,0),(1,'000111001','Foglie alterne',7,0),(1,'0010','Foglie ovali, elittiche o lanceolate',NULL,0),(1,'0011','Foglie non intere',NULL,0),(1,'00110','Foglie pennate, partite, sette o laciniate',NULL,0),(1,'00111','Foglie palmate',NULL,0),(1,'00100','Infiorescenze a capolino',NULL,0),(1,'00101','Fiori semplici',NULL,0),(1,'001000','Foglie lungo il fusto',NULL,0),(1,'001001','Rosette basali',9,0),(1,'0010000','Foglie alterne',9,0),(1,'0010001','Foglie opposte',9,0),(1,'001010','Corolle raggiate',NULL,0),(1,'001011','Corolle non raggiate (zigomorfe)',NULL,0),(1,'0010100','Foglie singole',NULL,0),(1,'0010101','Foglie raggruppate',NULL,0),(1,'00101010','Foglie in rosette basali',11,0),(1,'00101011','Foglie verticillate',11,0),(1,'00101000','Foglie alterne',NULL,0),(1,'00101001','Foglie opposte',11,0),(1,'001010000','Infiorescenze a cima',10,0),(1,'001010001','Infiorescenze non a cima',10,0),(1,'001100','Infiorescenze a capolino',NULL,0),(1,'0010110','Foglie opposte',12,0),(1,'0010111','Foglie alterne',12,0),(1,'001101','Fiori semplici',NULL,0),(1,'0011000','Foglie opposte',13,0),(1,'0011001','Foglie alterne',13,0),(1,'0011010','Corolle raggiate, foglie opposte',14,0),(1,'0011011','Corolle non raggiate (zigomorfe)',NULL,0),(1,'00110110','Foglie opposte',14,0),(1,'00110111','Foglie alterne',14,0),(1,'001110','Infiorescenze a capolino',15,0),(1,'001111','Fiori singoli',NULL,0),(1,'0011110','Corolle raggiate',NULL,0),(1,'0011111','Corolle non raggiate (zigomorfe)',NULL,0),(1,'00111100','Piante volubili',15,0),(1,'00111101','Piante non volubili',15,0),(1,'00111110','Foglie opposte',16,0),(1,'00111111','Foglie alterne',16,0),(1,'010','Piante con clorofilla non carnose, non squamiformi ne scagliose',NULL,0),(1,'011','Piante senza clorofilla o carnose, squamiformi, scagliose',NULL,0),(1,'0100','Corolle raggiate',NULL,0),(1,'0101','Corolle non raggiate (zigomorfe)',20,0),(1,'0110','Piante con foglie carnose cilindriche o piante',18,0),(1,'0111','Piante non carnose',NULL,0),(1,'01110','Piante senza clorofilla, senza colore verde',18,0),(1,'01111','Piante con foglie aghiformi o a squama',NULL,0),(1,'011110','Fiori evidenti',17,0),(1,'011111','Fiori poco appariscenti',17,0),(1,'01000','Foglie opposte',19,0),(1,'01001','Foglie alterne o in rosette',NULL,0),(1,'010010','Capolini',19,0),(1,'010011','Fiori singoli',NULL,0),(1,'0100110','Petali 5',19,0),(1,'0100111','Petali o lobi corollini 6',NULL,0),(1,'01001110','Ovario supero',19,0),(1,'01001111','Ovario infero',20,0),(1,'10','Foglie a nervature pennate o palmate (non parallele)',NULL,0),(1,'11','Foglie a nervature parallele (lanceolate o lineari)',NULL,0),(1,'110','Piante non graminiformi',21,0),(1,'111','Piante graminiformi',NULL,0),(1,'1110','Fusti senza nodi',22,0),(1,'1111','Fusti con nodi',22,0),(1,'100','Foglie intere',NULL,0),(1,'101','Foglie lobate, sette o palmate',NULL,0),(1,'1000','Foglie astate o lanceolate, alterne',NULL,0),(1,'1001','Foglie lanceolate',NULL,0),(1,'10000','Piante con lattice bianco',23,0),(1,'10001','Piante senza lattice bianco',23,0),(1,'10010','Foglie alterne',24,0),(1,'10011','Foglie opposte',24,0),(1,'1010','Foglie lobate, pennato partite, sette o laciniate',25,0),(1,'1011','Foglie palmato partite, sette o biternate',26,0),(2,'110','Fiore raggiato - Foglie opposte - Petali 4',32,5),(2,'11','Fiore raggiato - Foglie opposte - Calice a sepali liberi',31,4),(2,'10','Fiore raggiato - Foglie opposte - Calice tuboloso',30,3),(2,'1','Fiore raggiato - Foglie opposte - Petali 5',28,2),(2,'0','Fiore raggiato - Foglie opposte - Petali 6',27,1),(2,'111','Fiore raggiato - Foglie opposte - Petali 4',33,6),(3,'111','Corolle non raggiate - Fiori non speronati',37,12),(3,'110','Corolle non raggiate - Fiori non speronati',36,11),(3,'11','Corolle non raggiate - Fiori speronati',35,10),(3,'10','Corolle non raggiate - Fiori speronati',34,9),(3,'0','Fiore raggiato - Foglie alterne o basali - Petali 6',27,7),(3,'1','Fiore raggiato - Foglie alterne o basali - Petali 5',31,8),(4,'11','Fiori raggiati - Petali 5 - Foglie opposte - Fiori gialli, frutti spinosi',39,16),(4,'1','Fiori non raggiati',38,14),(4,'10','Fiori raggiati - Petali 4 - Stami 6',33,15),(4,'0','Fiori raggiati - Petali 4 - Stami molti',38,13),(4,'110','Fiori raggiati - Petali 5 - Foglie opposte - Fiori violetti, frutti a becco di cicogna',40,17),(5,'1','Fiore raggiato - foglie incise, lobate o laciniate',42,19),(5,'10','Fiore raggiato (Ruta graveolensis)',43,20),(5,'11','Resedaceae (<em>Reseda lutea</em>)',44,21),(5,'0','Fiore raggiato - Infiorescenze non a ombrella o fiori solitari, foglie alterne',41,18),(5,'110','Infiorescenza a ombrella, foglie alterne da pennatosette a laciniate',45,22),(6,'111','Fiore raggiato (<em>Parnassia palustris</em>)',46,28),(6,'11','Fiore non raggiato',34,26),(6,'110','Fiore raggiato (<em>Ranunculus ficaria</em>)',42,27),(6,'10','Fiori speronati, foglie partite o sette',34,25),(6,'1','Petali subeguali tra loro (<em>Dictamus albus</>)',43,24),(6,'0','Petali molto diversi tra di loro',36,23),(7,'10','Foglie alterne o basali - Margine regolarmente seghettato',41,31),(7,'0','Foglie opposte',40,29),(7,'1','Foglie alterne o basali - Stami liberi - Margine irregolarmente dentato o foglie laciniate',42,30),(7,'11','Foglie alterne o basali - Stami riuniti in tubo attorno a stilo',47,32),(8,'10','Fiore raggiato - Margine segnetto o dentato',47,35),(8,'1','Fiore raggiato - Margine segnetto o dentato',41,34),(8,'0','Fiore raggiato - Margine liscio',48,33),(8,'11','Fiore non raggiato',36,36),(9,'11','Foglie opposte',51,40),(9,'10','Foglie alterne o basali',50,39),(9,'1','Rosette basali',50,38),(9,'0','Rosette basali',49,37),(9,'110','Foglie opposte',50,41),(10,'11','Infiorescenza non a cima - Foglie a margine liscio',55,45),(10,'110','Infiorescenza non a cima - Foglie a margine liscio',60,46),(10,'10','Infiorescenza non a cima - Foglie a margine dentato o segato',54,44),(10,'0','Infiorescenze a cima',52,42),(10,'1','Infiorescenza non a cima - Foglie a margine dentato o segato',53,43),(11,'1110','Foglie verticillate',57,53),(11,'111','Rosette basali',57,52),(11,'110','Rosette basali',61,51),(11,'11','Foglie opposte',60,50),(11,'10','Foglie opposte',59,49),(11,'1','Foglie opposte',58,48),(11,'0','Foglie opposte',57,47),(11,'1111','Foglie verticillate',62,54),(12,'11','Foglie alterne o basali',52,58),(12,'1','Foglie opposte - Fusto più o meni cilindrico',64,56),(12,'10','Figlie alterne o basali',64,57),(12,'0','Foglie opposte - Fusto a 4 angoli',63,55),(13,'1','Foglie opposte',50,60),(13,'0','Foglie opposte',51,59),(13,'10','Foglie alterne o basali',50,61),(14,'111','Fiore raggiato - foglie altrene o basali',64,67),(14,'110','Fiore raggiato - foglie opposte',67,66),(14,'11','Fiore raggiato - foglie opposte',63,65),(14,'10','Fiore raggiato - foglie opposte',66,64),(14,'1','Fiore raggiato - foglie opposte',58,63),(14,'0','Fiore raggiato - foglie opposte',65,62),(15,'10','Fiore raggiato - piante volubili',54,70),(15,'1','Fiore raggiato - piante volubili',69,69),(15,'0','Infiorescenza a capolino',50,68),(15,'11','Fiore raggiato - piante non volubili',60,71),(16,'1','Foglie alterne o basali - corolla a 4-5 lobi',64,73),(16,'10','Foglie alterne o basali - corolla a 1 lobo',70,74),(16,'0','Foglie opposte',63,72),(16,'11','Foglie alterne o basali - corolla a 1 lobo',71,75),(19,'110','Petali 6 - Ovario supero',73,80),(19,'11','Foglie alterne o basali - Infiorescenze a capolino',50,79),(19,'10','Foglie alterne o basali - Petali 5',31,78),(19,'0','Foglie opposte',30,76),(19,'1','Foglie alterne o basali - Petali 5',72,77),(20,'10','Fiore non raggiato',76,83),(20,'1','Fiore raggiato - stami 3',75,82),(20,'0','Fiore raggiato - stami 6',74,81),(20,'11','Fiore non raggiato',75,84),(20,'110','Fiore non raggiato',77,85),(18,'110','Piante senza clorofilla - Volubili',54,90),(18,'11','Piante senza clorofilla - Non volubili',81,89),(18,'1','Piante con foglie carnose cilindriche o piane',79,87),(18,'10','Piante con foglie carnose cilindriche o piane',80,88),(18,'0','Piante con foglie carnose cilindriche o piane',78,86),(17,'110','Fiore poco appariscente',30,95),(17,'111','Fiore poco appariscente',62,96),(17,'11','Fiore poco appariscente',83,94),(17,'10','Fiore evidente',82,93),(17,'1','Fiore evidente',73,92),(17,'0','Fiore evidente',29,91),(23,'10','Piante senza lattice bianco',83,99),(23,'1','Piante senza lattice bianco',85,98),(23,'0','Piante con latice bianco',84,97),(23,'11','Piante senza lattice bianco',86,100),(24,'1','Foglie alterne o basali',50,102),(24,'10','Foglie alterne o basali (<em>Parietaria</em>)',87,103),(24,'11','Foglie opposte (<em>Mercurialis</em>)',84,104),(24,'110','Foglie opposte',58,105),(24,'111','Foglie opposte',62,106),(24,'0','Foglie alterne o basali',45,101),(25,'11',' ',45,110),(25,'10',' ',41,109),(25,'1',' ',50,108),(25,'0',' ',33,107),(25,'110',' ',88,111),(26,'110',' ',42,116),(26,'11',' ',89,115),(26,'10',' ',50,114),(26,'1',' ',50,113),(26,'0',' ',41,112),(26,'111',' ',45,117),(21,'111',' ',91,123),(21,'110',' ',45,122),(21,'11',' ',62,121),(21,'10',' ',90,120),(21,'1',' ',90,119),(21,'0',' ',42,118),(21,'1110',' ',92,124),(22,'111','Fusti con nodi',98,130),(22,'110','Fusti con nodi',97,129),(22,'11','Fusti con nodi',96,128),(22,'10','Fusti con nodi',95,127),(22,'1','Fusti senza nodi',94,126),(22,'0','Fusti senza nodi',93,125),(22,'1110','Fusti con nodi',99,131),(92,'0','Fusti non ramificati, giallo-bruni, con all\'apice lo strobilo fertile',0,0),(92,'00','fusti di 0 di 1 -2 cm',100,0),(92,'01','fusti di 0 di ½ cm',101,0),(92,'1','Fusti ramificati in verticilli, verdi, con o senza strobilo fertile',0,0),(92,'10','fusti principali con inlernodi color avorio; rami verdi',100,0),(92,'11','fusti con internodi verdi',0,0),(92,'110','rami pieni, semplici o ramificati 2-3 volte',101,0),(92,'111','rami cavi',0,0),(92,'1110','fusti + lisci',102,0),(92,'1111','fusti ruvidi',103,0);
/*!40000 ALTER TABLE `dico_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `add_dico`
--

DROP TABLE IF EXISTS `add_dico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `add_dico` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `taxa_id` int(11) DEFAULT NULL COMMENT 'Taxa id',
  `name` varchar(200) DEFAULT NULL COMMENT 'Name',
  `is_list` smallint(1) NOT NULL COMMENT 'Is list',
  PRIMARY KEY (`id`),
  KEY `taxa_id` (`taxa_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Additional dico';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `add_dico`
--

LOCK TABLES `add_dico` WRITE;
/*!40000 ALTER TABLE `add_dico` DISABLE KEYS */;
/*!40000 ALTER TABLE `add_dico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `add_dico_item`
--

DROP TABLE IF EXISTS `add_dico_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `add_dico_item` (
  `dico_id` int(11) NOT NULL,
  `id` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `taxa_id` int(11) DEFAULT NULL,
  `photo_id` int(11) NOT NULL COMMENT 'Photo id',
  PRIMARY KEY (`id`,`dico_id`),
  KEY `fk_dico_item_taxa_idx` (`taxa_id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Dicotomy item';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `add_dico_item`
--

LOCK TABLES `add_dico_item` WRITE;
/*!40000 ALTER TABLE `add_dico_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `add_dico_item` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-06-24 16:53:20
