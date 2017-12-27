<ol>
<li>Before starting make sure git branch master is committed and clean.<br/>
<strong>FTPLOY</strong> handles push to server.thinkitfirst.com as changes are committed to git.</li>
<p></p>
<li>Run shell <strong>pull-database.sh</strong> script in root of website to pull the <strong>pflwpress.sql</strong> & <strong>pflmicro.sql</strong><br/>
It will save two files into /database-backup with name+date.sql <br/>
If you run it twice in one day ti will override the existing file.<br/>
</li>
<p></p>
<li>Insert Database to Production</li>
<li> SSH into ASO Server :   ssh possefootball@207.210.192.147</li>
<li> Type Password from Meldium Ft4,-Wh{~5m#</li>

// close but not there yet...... 
Open Terminal through SSH on server.thinkitfirst.com (password in Meldium)

ssh possefootball@207.210.192.147 mysql -u possefoo_test -p=X1xv83pxbWe possefoo_test < firsttable.sql

mysql -u possefoo_data -pBeTS^[MG@,0n possefoo_data < pflmicro_000016.sql
mysql -u possefoo_wp -ppC8)@Mq1dZVl- possefoo_wp < pflwpress_000016.sql



</ol>







