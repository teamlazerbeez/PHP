-- Server version	5.0.56sp1-enterprise-gpl-log

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
-- Table structure for table `ut_active_records`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ut_active_records` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `int_allow_null` int(10) default NULL,
  `int_not_allow_null` int(10) NOT NULL,
  `string_allow_null` varchar(40) default NULL,
  `string_not_allow_null` varchar(40) NOT NULL,
  `enum_with_null` enum('foo','bar','null') default NULL,
  `enum_not_null` enum('foo','bar') NOT NULL,
  `int_default_1` int(11) default '1',
  `int_not_null_default_2` int(11) NOT NULL default '2',
  `string_default_ls` varchar(16) default 'ls',
  `string_not_null_default_ps` varchar(16) NOT NULL default 'ps',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=220806 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `ut_active_records_2`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ut_active_records_2` (
  `contactID` int(10) unsigned NOT NULL,
  `fieldID` int(10) unsigned NOT NULL,
  `value` varchar(128) default NULL,
  PRIMARY KEY  (`contactID`,`fieldID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='test table for Marshall';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `ut_active_records_3`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ut_active_records_3` (
  `id` int(10) unsigned NOT NULL default '0',
  `value` varchar(16) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `ut_active_records_4`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ut_active_records_4` (
  `customerID` int(11) default NULL,
  `teamID` int(11) default NULL,
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

--
-- Table structure for table `fixture_test`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fixture_test` (
  `i1` int(11) default NULL,
  `s1` varchar(255) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
