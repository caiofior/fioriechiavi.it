#!/bin/bash
mysqldump -u root --no-data fioriech65618 | sed 's/ AUTO_INCREMENT=[0-9]*//g' > 1.sql 
