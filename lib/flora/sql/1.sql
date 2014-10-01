-- MySQL dump 10.13  Distrib 5.5.38, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: flora
-- ------------------------------------------------------
-- Server version	5.5.38-0ubuntu0.14.04.1

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
-- Table structure for table `dico`
--

DROP TABLE IF EXISTS `dico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='Dicotomy';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dico`
--

LOCK TABLES `dico` WRITE;
/*!40000 ALTER TABLE `dico` DISABLE KEYS */;
INSERT INTO `dico` VALUES (4),(5),(6),(7);
/*!40000 ALTER TABLE `dico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dico_item`
--

DROP TABLE IF EXISTS `dico_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dico_item` (
  `id` varchar(100) NOT NULL,
  `id_dico` int(11) NOT NULL,
  `text` text NOT NULL,
  `taxa_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`,`id_dico`),
  KEY `fk_dico_item_idx` (`id_dico`),
  KEY `fk_dico_item_taxa_idx` (`taxa_id`),
  CONSTRAINT `fk_dico_item` FOREIGN KEY (`id_dico`) REFERENCES `dico` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dico_item_taxa` FOREIGN KEY (`taxa_id`) REFERENCES `taxa` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dicotomy item';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dico_item`
--

LOCK TABLES `dico_item` WRITE;
/*!40000 ALTER TABLE `dico_item` DISABLE KEYS */;
INSERT INTO `dico_item` VALUES ('0',4,'Piante senza veri fi.; riproduzione mediante spore',8),('0',5,'Fg. ridotte a squame brune; funzione assimilatoria svolta dai rami; arbusti con aspetto d\'Equiseti e generalm. con rami fragili',12),('0',6,'Fg. appiattite disposte in due serie opposte; fg. erette',14),('0',7,'Fg. lunghe 10-20 mm con cicatrice elittica',15),('1',4,'Piante con fi.; mediante semi',NULL),('1',5,'Fg. aghiformi oppure squamose (ma intal caso verdi) o con lamina sviluppata; arbusti o alberi mai con aspetto d\'Equiseti o rami fragili',NULL),('1',6,'Fg. a sezione rombica, disposte tutt\'attorno ai rami; pigne pendule',NULL),('1',7,'Fg. lunghe 10-13 mm con cicatrice circolare',NULL),('10',4,'Fi. privi di perianzio; ovuli inseriti direttam. su squame; mancano ovario, stilo e stimma',9),('10',5,'Fg. aghiformi o squamose (oppure i due tipi nella stessa pianta) o con lamina sviluppata; fr. a cono (o in <i>Juniperus</i> frutti a bacca con polpa secca e coriacea)',NULL),('10',6,'Fg. a 2-5',NULL),('100',5,'Fg. alternate o riunite a 2-40 brachiblasti',13),('101',5,'Fg. opposte o verticillate a 2-4',NULL),('11',4,'Fi. generalm. provvisti di perianzio; ovuli inclusi in un ovario, generalm. sormontato da stilo e stimma, o almeno da uno di questi due organi (Angiosperme)',NULL),('11',5,'Fg. con lamina appiattita; fr. circondato da polpa molle e acquosa',NULL),('11',6,'Fg. a 15-40',NULL),('110',4,'Fg. penninervie o palminervie; f. con fasci ordinati radialemnte; fi. 4meri o 5meri; semi con 2 cotiledoni',10),('110',5,'Fg. lineari larghe 2 mm, sempreverdi; fr. diam. 6 mm',NULL),('110',6,'Fg. caduche d\'inverno; pigne lunghe 2-3 cm; pianta spontanea',NULL),('111',4,'Fg, parallelinervie; f.senza vera corteccia e con faci disposti disordinatam. (sezionare!); fi.generalm. 3 meri; semi con 1 cotiledone',11),('111',5,'Fg. a ventaglio flabellate larghe 3-6 cm, caduche; fr. diametro 25-30 mm',NULL),('111',6,'Fg. sempreverdi; pigne lunghe 3-12 cm; pianta coltivata',NULL);
/*!40000 ALTER TABLE `dico_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Profile data',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Profile';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile`
--

LOCK TABLES `profile` WRITE;
/*!40000 ALTER TABLE `profile` DISABLE KEYS */;
INSERT INTO `profile` VALUES (2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'caiofior@gmail.com');
/*!40000 ALTER TABLE `profile` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Region description';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `region`
--

LOCK TABLES `region` WRITE;
/*!40000 ALTER TABLE `region` DISABLE KEYS */;
INSERT INTO `region` VALUES ('01','Piemonte'),('02','Valle d\'Aosta/VallÃ©e d\'Aoste'),('03','Lombardia'),('04','Trentino-Alto Adige/SÃ¼dtirol'),('05','Veneto'),('06','Friuli-Venezia Giulia'),('07','Liguria'),('08','Emilia Romagna'),('09','Toscana'),('10','Umbria'),('11','Marche'),('12','Lazio'),('13','Abruzzo'),('14','Molise'),('15','Campania'),('16','Puglia'),('17','Basilicata'),('18','Calabria'),('19','Sicilia'),('20','Sardegna');
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
  `dico_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_taxonomy_kind_idx` (`taxa_kind_id`),
  CONSTRAINT `fk_taxonomy_kind` FOREIGN KEY (`taxa_kind_id`) REFERENCES `taxa_kind` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='Taxa';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa`
--

LOCK TABLES `taxa` WRITE;
/*!40000 ALTER TABLE `taxa` DISABLE KEYS */;
INSERT INTO `taxa` VALUES (8,13,'Pteridofite','',0),(9,13,'Gimnosperme','<p>Piante con fase diploide prevalente rispetto all\'aploide, quest\'ultima rappresentata da un solo organello della prima; sporofito con tessuto vascolare ben differenziato; fiori rudimentali con ovuli inseriti su macrosporofilli, senza ovario ne stimma; fencodaz. mediante tubo pollinico oppure spermatozoidi (non nelle nostre specie).</p>',5),(10,14,'Monocotiledoni','',0),(11,14,'Dicotiledoni','',0),(12,7,' Ephedraceae','',0),(13,7,'Pinacee','<p style=\"margin-bottom: 0cm; line-height: 100%;\">Fi. non avvolti da bratee; inflor. â™‚ con numerosi stami squamiformi spiralati; inflor. â™€ con numerose squame spiralate, ciascuna portante alla base due ovuli; semi alati. Alberi con fg. aghiformi; anemogamia</p>',6),(14,12,'Abies','',7),(15,15,'Abies alba','<p>Corteccia bianco grigiastra o grigio pallida, desquamante in piastre sottili. Rami bruno scuri, i giovani rossastri e pubescenti. Fg. lineari appiattite (1,5-2 x 10-20 mm), inserite tutt\'intorno ai rami, ma tutte rivolte sullo stesso lato, ottuse all\'apice, scanalate verso la nervatura centrale, con due linee longitudinali bianche sotto. Pigne erette, fino a 4 x 9 cm</p>',0);
/*!40000 ALTER TABLE `taxa` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='Taxa attribute';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_attribute`
--

LOCK TABLES `taxa_attribute` WRITE;
/*!40000 ALTER TABLE `taxa_attribute` DISABLE KEYS */;
INSERT INTO `taxa_attribute` VALUES (1,'Nome Italiano',NULL),(3,'Portamento',NULL),(4,'Altezza',NULL);
/*!40000 ALTER TABLE `taxa_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxa_attribute_value`
--

DROP TABLE IF EXISTS `taxa_attribute_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxa_attribute_value` (
  `id_taxa` int(11) NOT NULL COMMENT 'Id of taxa',
  `id_taxa_attribute` int(11) NOT NULL COMMENT 'Id of taxa attribute',
  `value` varchar(100) DEFAULT NULL COMMENT 'Value',
  PRIMARY KEY (`id_taxa`,`id_taxa_attribute`),
  KEY `fk_taxa_attribute_value_taxa_attribute` (`id_taxa_attribute`),
  KEY `fk_taxa_attribute_value_taxa_id` (`id_taxa`),
  CONSTRAINT `fk_taxa_attribute_value_taxa` FOREIGN KEY (`id_taxa`) REFERENCES `taxa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_taxa_attribute_value_taxa_attribute` FOREIGN KEY (`id_taxa_attribute`) REFERENCES `taxa_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Taxa attribute value';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_attribute_value`
--

LOCK TABLES `taxa_attribute_value` WRITE;
/*!40000 ALTER TABLE `taxa_attribute_value` DISABLE KEYS */;
INSERT INTO `taxa_attribute_value` VALUES (15,1,'Abete bianco'),(15,3,'Arboreo'),(15,4,'10 - 40 m');
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
  `id_taxa` int(11) DEFAULT NULL COMMENT 'Id of taxa',
  `filename` varchar(200) DEFAULT NULL COMMENT 'Filename',
  PRIMARY KEY (`id`),
  KEY `fk_taxa_image_1_idx` (`id_taxa`),
  CONSTRAINT `fk_taxa_image_1` FOREIGN KEY (`id_taxa`) REFERENCES `taxa_image` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Taxa images';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_image`
--

LOCK TABLES `taxa_image` WRITE;
/*!40000 ALTER TABLE `taxa_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxa_image` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='Taxa kind';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_kind`
--

LOCK TABLES `taxa_kind` WRITE;
/*!40000 ALTER TABLE `taxa_kind` DISABLE KEYS */;
INSERT INTO `taxa_kind` VALUES (7,3,'Fam.','Famiglia',''),(12,4,'Gen.','Genere',''),(13,1,'Div.','Divisione',''),(14,2,'Cla.','Classe',''),(15,5,'Sp.','Specie','');
/*!40000 ALTER TABLE `taxa_kind` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxa_region`
--

DROP TABLE IF EXISTS `taxa_region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxa_region` (
  `id_taxa` int(11) NOT NULL,
  `id_region` varchar(20) NOT NULL,
  PRIMARY KEY (`id_taxa`,`id_region`),
  KEY `fk_taxa_region_region_idx` (`id_region`),
  KEY `fk_taxa_region_taxa_idx` (`id_taxa`),
  CONSTRAINT `fk_taxa_region_region` FOREIGN KEY (`id_region`) REFERENCES `region` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_taxa_region_taxa` FOREIGN KEY (`id_taxa`) REFERENCES `taxa` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Association between taxa and region';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxa_region`
--

LOCK TABLES `taxa_region` WRITE;
/*!40000 ALTER TABLE `taxa_region` DISABLE KEYS */;
INSERT INTO `taxa_region` VALUES (15,'01'),(15,'02'),(15,'03'),(15,'04'),(15,'05'),(15,'06'),(15,'07'),(15,'08'),(15,'09'),(15,'10'),(15,'11'),(15,'12'),(15,'13'),(15,'14'),(15,'15'),(15,'16'),(15,'17'),(15,'18');
/*!40000 ALTER TABLE `taxa_region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `username` varchar(100) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `active` smallint(6) DEFAULT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `creation_datetime` datetime DEFAULT NULL COMMENT 'user creation datetime',
  `change_datetime` datetime DEFAULT NULL COMMENT 'user last modify date time',
  `confirm_datetime` datetime DEFAULT NULL COMMENT 'confirm datet time',
  `last_login_datetime` datetime DEFAULT NULL,
  `confirm_code` varchar(50) DEFAULT NULL COMMENT 'confirm code',
  PRIMARY KEY (`username`),
  KEY `fk_user_role_idx` (`role_id`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `user_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('caiofior@gmail.com','8f04a464e3660e912e8abaf8aefaa2fe',1,2,1,'2014-09-01 15:00:27',NULL,NULL,NULL,'65965886c8ccb3ce8ded53c7d45a08be');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_role`
--

DROP TABLE IF EXISTS `user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_role` (
  `id` int(11) NOT NULL,
  `description` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User role';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_role`
--

LOCK TABLES `user_role` WRITE;
/*!40000 ALTER TABLE `user_role` DISABLE KEYS */;
INSERT INTO `user_role` VALUES (1,'Administrator'),(2,'Editor'),(3,'User');
/*!40000 ALTER TABLE `user_role` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-10-01 15:54:51
