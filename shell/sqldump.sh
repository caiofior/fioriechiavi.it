#!/bin/sh
mysqldump -u root fioriech65618 --no-data | sed 's/ AUTO_INCREMENT=[0-9]*//g' > ../install/sql/install.sql