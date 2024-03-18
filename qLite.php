
<?php

//    	qLite is free software: you can redistribute it and/or modify
//    	it under the terms of the GNU General Public License as published by
//    	the Free Software Foundation, either version 3 of the License, or
//    	(at your option) any later version.
//
//    	This program is distributed in the hope that it will be useful,
//    	but WITHOUT ANY WARRANTY; without even the implied warranty of
//    	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    	GNU General Public License for more details.
//		
//    	You should have received a copy of the GNU General Public License
//		along with this program.  If not, see <http://www.gnu.org/licenses/>.
//		Andrea Spanu: spanu_andrea(at)yahoo.it



	session_start();

	ini_set('max_execution_time', 0);
	
	$_SESSION["QLITE_PWD_REQUIRED"]=true;
	$_SESSION["QLITE_PWD_DB"]="qLite.sqlite3"; //THE ABSOLUTE PATH not THE NAME
	$_SESSION["QLITE_PWD_DB_NAME"]="main"; //THE  NAME to display
	$_SESSION["QLITE_ADMIN_NAME"]="admin";
	
	
	define ("QLITE_PWD_PWD","e52cbe1aa2f5cf7b68225ad60eb9ba0d5bc376c5481764d70929eb3d65d00512");
	define ("QLITE_HASH_ALG","sha256");
	
	define ('MAX_VIEW_LIMIT',1000);
	
	
	define ('CLR_RED','#FCBFB3');
	define ('CLR_GREEN','#AAFF88');
	define ('CLR_BLUE','#F6FFFD');	
	define ('CLR_GREY','#F6F7F3');
	define ('CLR_STRONG_RED','#FAB3AA');
	define ('CLR_STRONG_GREEN','#7FFF74');
	define ('CLR_STRONG_GREY','#EAEBE7');
	define ('CLR_STRONG_BLUE','#EAFFFB');
	define ('CLR_STRONG_STRONG_GREY','#DADBD8');
	
	
	$is_changed_db=false;
	if(isset($_POST["db_changed"]) && ! isset( $_POST["change_state"]))
				$is_changed_db=true;
	
	define('STATE_LOGIN', '1');
	define('STATE_CHANGE_PWD', '2');
	define('STATE_WORK', '4');
	define('STATE_UNKNOW', '8');
	
	$db_name=isset($_POST["db"])?$_POST["db"]:(isset($_GET["db"])?$_GET["db"]:NULL);

	 
	$input_user=isset($_POST["user"])?$_POST["user"]:(isset($_GET["user"])?$_GET["user"]:NULL);
	$vl=isset($_POST['view_limit'])?(MAX_VIEW_LIMIT<$_POST['view_limit']?MAX_VIEW_LIMIT:$_POST['view_limit']):100;
	
	$user_can_write=false;
	$state=STATE_LOGIN;
	$main_errmsg =NULL;
	$main_log=NULL;
	$service=NULL;
	
	
	
	
	define('ERR_WRONG_PWD', 'ERR:password/username non corrette');
	define('ERR_WRONG_DB', 'ERR:database inesistente o user non autorizzato');
	define('ERR_NO_EXISTS_DB', 'ERR:database non raggiungibile');
	define('ERR_OPSS', 'ERR:errore sconosciuto avvisare programmatore');
	
	
	define('LOG_DB_CHANGED', 'Sei ora  connesso a:');
	define('LOG_PWD_CHANGED', 'Password cambiata con successo');
	

	
	define('LBL_OLD_PWD', 'Vecchia Password');
	define('LBL_NEW_PWD', 'Nuova Password');
	define('LBL_REPEAT_PWD', 'Ripeti Password');
	define('LBL_NO_VALID_PWD', 'Password deve contenere <br>maiuscole, miniscole, numeri, punteggiatura<br> e lunga almeno 8 caratteri');
	define('LBL_NO_REPEAT', 'Password diverse');
	define('LBL_BACK', 'Annulla');
	define('LBL_MAX_ROWS_TO_SHOW', 'Max Numero Righe');
	define('LBL_ESCAPE', 'Formata SQL Testo');
	define('LBL_CHANGE_PWD', 'Cambia Password');
	define('LBL_LOGOUT', 'Disconneti');
	define('LBL_INS_CRE', 'Crea/Inserisci DB');
	define('LBL_WRITE_QUERY_HERE', '--Scrivi query qui');
	define ('COMMON_QRY',
		"CREATE TABLE IF NOT EXISTS  qLite_qry(id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL CHECK(length(name)>0), qry TEXT, adminqry INTEGER, grp TEXT DEFAULT ''); 
		CREATE UNIQUE INDEX qLite_ix_u_qry ON qLite_qry(upper(name),upper(grp));
		INSERT INTO  qLite_qry(name, qry,adminqry )  VALUES('master', 'SELECT    t.name TableName, c.name ColumnName ,c.type
			FROM sqlite_master t,PRAGMA_TABLE_INFO(t.name) c 
			ORDER BY  t.name,c.name',0);
		
		INSERT INTO  qLite_qry(name, qry,adminqry,grp )  
		VALUES('Query', 'INSERT INTO qLite_qry(name,qry,adminqry,grp) VALUES(''qry_name'',''qry_def'',0, ERR_GROUP_NAME)'
			,1,'insert');
		
		INSERT INTO  qLite_qry(name, qry,adminqry,grp )  VALUES('Query', 'DELETE FROM qLite_qry WHERE name=''qryname'';',1,'delete');
		INSERT INTO  qLite_qry(name, qry,adminqry,grp )  VALUES('Query', '
		UPDATE qLite_qry SET name=name,qry=qry,adminqry=adminqry,grp=grp WHERE name=''qryname''',1,'update');
		
		INSERT INTO  qLite_qry(name, qry,adminqry,grp )  
		VALUES('Query', 'SELECT id,name,qry,adminqry,grp FROM qLite_qry;'
			,0,'select');
		
		
		");
	
	
	
	$global_macros=array( array("{user}","s",$input_user),
							array("{current_db}","s",$db_name));
	
	$tags=array(array("s1",		"{s1}","s"),
				array("s2", 	"{s2}","s"),
				array("s3", 	"{s3}","s"),
				array("s4", 	"{s4}","s"),
				array("s5", 	"{s5}","s"),
				array("s6", 	"{s6}","s"),
				array("n1", 	"{n1}","n"),
				array("n2", 	"{n2}","n"),
				array("n3", 	"{n3}","n"),
				array("n4", 	"{n4}","n"),
				array("n5", 	"{n5}","n"),
				array("n6", 	"{n6}","n"),
				
				
				);
			
			
	$submits=array(array("b1","qry1"),
					array("b2", "qry2"),
					array("b3", "qry3"));
			
	$wdb="wdb";
	$ldb="ldb";
	$lqry="lqry";
	
	
function is_valid_class_name($cls)
{
	if( !(($cls[0]>="A" &&  $cls[0]<="Z")||  ($cls[0]>="a" &&  $cls[0]<="z")))
		return false;
	$l=strlen($cls);
	for($i=1;$i<$l;$i++)
		if( !(($cls[$i]>="A" &&  $cls[$i]<="Z")||  ($cls[$i]>="a" &&  $cls[$i]<="z")||  ($cls[$i]>="0" &&  $cls[$i]<="9")
			|| $cls[$i]=='-' ||$cls[$i]='_') ) 
			return false;
	return true;
}
	
	
	
class dbsqlite3 extends SQLite3
{
	private $_res=NULL;
	private $_is_valid_db=false;
	private $_dbpath=NULL;
	private $_dbname=NULL;
	private $_dbid=NULL;
public function __construct($dbpath, $mode=SQLITE3_OPEN_READONLY)
{	
	
	try 	{
				$this->open($dbpath,$mode);
				$this->_is_valid_db=true;
				$this->_dbpath = $dbpath;
				$this->enableExceptions(true);
				 
		} catch (Exception $e) {
			echo   $e->getMessage() .'<br>';
			$this->_is_valid_db=false;
		}
	
		
	 
}
public function is_valid_db()
{
	return $this->_is_valid_db;
}


public function escape($data) {
        if(is_array($data))
            return array_map("sqlite_escape_string", $data);

        return sqlite_escape_string($data);
    } 

public function get_err_msg()
{
	return $this->lastErrorMsg();
}
public function query_command($qry)
{
	try 	{
		 
			$_res=$this->exec($qry);
			if(is_object($_res))
				$_res->finalize();
			return true;
		} catch (Exception $e) {
			 
			return  '<span class="qryerr">'. $e->getMessage() .'</span>';
		}
}
public function query_arr($qry, $mode=SQLITE3_BOTH)
{
	try 	{
				$this->_res=$this->query($qry);
				if(is_object($this->_res))
					return $this->_res->fetchArray($mode);
				else
					return $this->_res;
		} catch (Exception $e) {
			return  '<span class="qryerr">'. $e->getMessage() .'</span>';
		}
}
public function get_db_path()
{
	return $this->_dbpath;
}
public function get_db_name()
{
	return  $this->_db_name;
	
}

public function get_dbid()
{
	return  $this->_dbid;
	
}

public function build($pwddb,$db_path )
{
		
		if (!$this->is_valid_db())
				return false ;	
		
		$qry="SELECT * FROM qLite_qry;";
		
		if( !($r=$this->query_arr($qry))|| !is_array($r ))
		{
			
			$this->query_command(COMMON_QRY);
		}
		else 
			$this->free();
		
		$qry="SELECT id,db FROM dbs WHERE path=" .str_replace("'","''",$db_path);
		
		$r=$pwddb->query_arr($qry);
		if(!$r || !is_array($r))
		{
				$qry="INSERT INTO dbs (db,path) VALUES ('" . str_replace("'","''",$this->_dbpath) . "','" . str_replace("'","''",$this->_dbpath) . "')	";
				if(!$pwddb->query_command($qry)) return false;
				$this->_dbid=$pwddb->lastInsertRowID();
				$this->_dbname=$this->_dbpath;
		}
		else 
		{
			$this->_dbid=$r["id"];
			$this->_dbname=$r["db"];
			
		}
		 
		$qry="SELECT * FROM grants WHERE id_u=" .$pwddb->get_admin_id() . " AND id_d =" . $this->_dbid ;
		$r=$pwddb->query_arr($qry);
		if(!$r || !is_array($r))	
		{
			$qry="INSERT INTO grants (id_u,id_d,canwrite) VALUES (" . $pwddb->get_admin_id() ."," . $this->_dbid . ", 1);";
			$pwddb->query_command($qry);
		}
		else
			$pwddb->free();
		return true;
}

public function next($mode=SQLITE3_BOTH)
{
	try 	{
			if ($this->_res)
				return $this->_res->fetchArray($mode);
			else
				return $this->_res;
			
		} catch (Exception $e) {
			return   $e->getMessage() .'<br>';
		}
}
public function free()
{
	try 	{
			$this->_res->finalize();
			$this->_res=NULL;
		} catch (Exception $e) {
			return   $e->getMessage() .'<br>';
		}
}
public function load_file($fname)
{
	
	$qry="CREATE TABLE IF NOT EXISTS pag 
		(id INTEGER PRIMARY KEY AUTOINCREMENT,
		titolo TEXT,
		autore TEXT,
		linea INTEGER,
		chiusa INTEGER DEFAULT 0);
		";
		if($this->query_command($qry)!=true)
			return  $this->lastErrorMsg();
		
	$qry="CREATE TABLE IF NOT EXISTS par 
		(id_p INTEGER ,
		par TEXT,
		row INTEGER,
		col INTEGER,
		FOREIGN KEY(id_p) REFERENCES pag(id) ON DELETE CASCADE);";
	if($this->query_command($qry)!=true)
		return  $this->lastErrorMsg();;
	
	
	
	$fdb=fopen($fname,"rb");
	if(!$fdb) 
		return "Impossibile aprire il file" .$fname ;
	
	
	if($this->query_command("BEGIN;")!=true)
	{
		fclose($fdb);
		return  $this->lastErrorMsg();
	}	
	
	
	
	$j=1;
	$l=0;
	$ord0=ord('0');
	$stmpag=$this->prepare("INSERT INTO pag(titolo,autore,linea) VALUES (:t,:a,:l)");
	$stmpar=$this->prepare("INSERT INTO par(id_p,par, row,col ) VALUES(:id,:p,:r,:c)");
	while( $rec=fgets($fdb))
	{	
		$rec=trim($rec);
		$rw=explode('##',$rec);
	
		if(count($rw)<3)
			return "Riga:" . $j ." non correttamente formata" ;
		 $j++;
		$l++; 
		$stmpag->bindValue(':t',$rw[0],SQLITE3_TEXT);
		$stmpag->bindValue(':a',$rw[1],SQLITE3_TEXT);
		$stmpag->bindValue(':l',$l,SQLITE3_INTEGER);
		if(!$stmpag->execute())
			return $this->lastErrorMsg();
		
		$len =strlen($rw[2]);
		$i=0;
		$lastid=$this->lastInsertRowID();
		while($i<$len)
		{
			 	
			$stmpar->bindValue(':id',$lastid,SQLITE3_INTEGER);
			$stmpar->bindValue(':p',$rw[2][$i],SQLITE3_TEXT);
			++$i;
			$rc=0;
			while($i<$len && $rw[2][$i]>='0' && $rw[2][$i]<='9' )
			{
				$rc=$rc *10 + ord($rw[2][$i])-$ord0;
				++$i; 
			}
			 
		 	$stmpar->bindValue(':r',$rc/10000,SQLITE3_INTEGER);
		 	$stmpar->bindValue(':c',$rc%10000,SQLITE3_INTEGER);
			if(!$stmpar->execute())
				return $this->lastErrorMsg();
			
		}	
		
	}
		fclose($fdb);
	
	if($this->query_command("COMMIT;")!=true)
	{
		 
		return  $this->lastErrorMsg();
	}
	
	return NULL;
}


public function arr_to_table($qry, $n)
{  
	$arr= false;
	try {
			$arr=$this->query($qry);
		
		} catch (Exception $e) {
		 
		return  '<span class="qryerr">'. $e->getMessage() .'</span>';
		}
	if(!$arr)
	{
		return '';
	}if(!is_object($arr)|| $arr->numColumns()==0   )
		return '(' . $this->changes() . ' rows affected)';
	
	$cls="";
	
	$cols="";
	
	$esc_cls=array();

	$sf_col=array();
	for($i=0;$i<$arr->numColumns();$i++)
	{
			$sf=str_replace("<","&lt;",$arr->columnName($i));
			$sf=str_replace(">","&gt;",$sf);
			array_push($esc_cls, array(is_valid_class_name($sf),$sf));
			array_push($sf_col,$sf);
			
			$cols.= '<th onclick="try{on_tbl_clk(0,'. $i .');}catch{}" class="' .
				($esc_cls[$i][0]?$esc_cls[$i][1]:''). '">'. $sf . '</th>';  
			
			$cls.=$arr->columnName($i);
	}
	
	$tbl_id_cls=is_valid_class_name($cls)?$cls:'';
	
	$res='<table  name="anstbl" class="anstbl '. $tbl_id_cls . '"><thead> <tr>
	' . $cols . '</tr> </thead>';
	
	$rn=1;
	$cl=0;
	while( ($row=$arr->fetchArray(SQLITE3_ASSOC)) && $rn<$n )
	{
		
		$res.='<tr>';
		foreach ($row as $fld)
		{
			  
				$sf=str_replace("<","&lt;",is_null($fld)?"":$fld);
				$sf=str_replace(">","&gt;",$sf);
				$sf=str_replace("\n","<br>",$sf);
				
				$res.='<td onclick="try{on_tbl_clk('. $rn .','. $cl .');}catch{} " class="' .
				($esc_cls[$cl][0]?$esc_cls[$cl][1]:'') . '" >'. (is_null($sf)?'ass':$sf) . '</td>';
				
		 	 ++$cl;
		}
		$res.='</tr>';	
		++$rn;
		$cl=0;
	}
	$res .='</table>';
	$arr->finalize();
	return $res;
}
}
class pwd_db3 extends dbsqlite3
{
	private $db_path; 
	private $last_err;
	private $admin_id;
	private $pwd_db_id;
	private $_is_valid_db;
	public function __construct($dbpath)
	{	
		try{
				$this->open($dbpath,SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
				$this->db_path=$dbpath;
				$this->enableExceptions(true);
				$this->build_base();
				$qry="SELECT id FROM users WHERE user='" . $_SESSION["QLITE_ADMIN_NAME"] ."'";
				if( ($r=$this->query_arr($qry)) && is_array($r))
				{
					$this->admin_id=$r[0];
					$this->free();
				}					
				$this->_is_valid_db=true;
				
				
		} catch (Exception $e) {
			echo   $e->getMessage() .'<br>';
			$this->_is_valid_db=false;
			return NULL;
		}
	}
	public function is_valid_db()
	{
		return $this->_is_valid_db;
	}
	public function get_admin_id()
	{
			return $this->admin_id;
	}
	public function build_base()
	{
		$qry="SELECT * FROM qLite_qry";
		
		if( is_array($r=$this->query_arr($qry)) && count($r)>0)
		{
			$this->free();
		}else
			{	

		
			$qry="
			CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY AUTOINCREMENT, 
			user TEXT NOT NULL CHECK(length(user)>0), 
			pwd TEXT,
			lev INTEGER
			);
			
			CREATE UNIQUE INDEX ix_u_u ON users(upper(user));
			
			CREATE TABLE IF NOT EXISTS dbs(id INTEGER PRIMARY KEY AUTOINCREMENT, 
				db TEXT NOT NULL CHECK(length(db)>0),
			 path TEXT NOT NULL CHECK(length(path)>0),
			 grp TEXT DEFAULT ''
			 );
			 
			 CREATE UNIQUE INDEX ix_u_d ON dbs(upper(db));
			
			CREATE TABLE IF NOT EXISTS grants(
			id_u INTEGER NOT NULL, 
			id_d INTEGER NOT NULL, 
			canwrite INTEGER NOT NULL, 
			FOREIGN KEY (id_u) REFERENCES users(id) ON DELETE CASCADE,
			FOREIGN KEY (id_d) REFERENCES dbs(id) ON DELETE CASCADE,
			UNIQUE(id_u,id_d));
			
			CREATE TABLE IF NOT EXISTS  menus( 
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name TEXT NOT NULL CHECK(length(name)>0),
			isif INTEGER DEFAULT 0 NOT NULL,
			cof TEXT CHECK( iif(isif>0, cof IS NOT NULL AND length(cof)>0,1)), 
			nameof TEXT DEFAULT '',
			grp TEXT DEFAULT '',
			description TEXT,
			UID INTEGER,
			dateid TEXT,
			db TEXT
			);
			
			CREATE UNIQUE INDEX ix_u_mn ON menus(upper(grp),upper(name),upper(coalesce(db,'')));
			
			CREATE TABLE IF  NOT EXISTS mtodu( 
			id_m INTEGER NOT NULL,
			id_d INTEGER, 
			id_u INTEGER,
			FOREIGN KEY (id_m) REFERENCES menus(id) ON DELETE CASCADE ,
			FOREIGN KEY (id_d) REFERENCES dbs(id) ON DELETE CASCADE,
			FOREIGN KEY (id_u) REFERENCES users(id) ON DELETE CASCADE
			);
			
			CREATE UNIQUE INDEX ix_u_mdu ON mtodu(id_m, coalesce(id_d,0),coalesce(id_u,0));
			
			CREATE TABLE IF NOT EXISTS utog
			(
				id_u INTEGER,
				id_g INTEGER,
				lev INTEGER DEFAULT 0,
				PRIMARY KEY (id_u,id_g),
				FOREIGN KEY (id_u,id_g) REFERENCES users(id,id) ON DELETE CASCADE 
			);
			";
			if(!$this->query_command($qry))
				echo $this->lastErrorMsg();
			//INSERT INTO users (user,pwd) VALUES ('admin','pwd useless');
			$qry="INSERT INTO users(user,pwd) VALUES('". $_SESSION['QLITE_ADMIN_NAME'] . "','pwd useless');";
			if(!$this->query_command($qry))
				echo $this->lastErrorMsg();
			$this->admin_id=$this->lastInsertRowID();
			
			$qry="INSERT INTO users(user,pwd) VALUES('public','pwd useless');
				  ";
			if(!$this->query_command($qry))
				echo $this->lastErrorMsg();
			
			if(!$this->query_command(COMMON_QRY))
				echo $this->lastErrorMsg();
			//INSERT INTO dbs(db,path) VALUES ( 'main','qLite.sqlite3');
			$qry="INSERT INTO dbs (db, path) VALUES ('" . $_SESSION["QLITE_PWD_DB_NAME"] . "','" . $_SESSION["QLITE_PWD_DB"] . "')"; 
			if(!$this->query_command($qry))
				echo $this->lastErrorMsg();
			
			$this->pwd_db_id=$this->lastInsertRowID();	
			//INSERT INTO grants (id_u,id_d,canwrite) VALUES (1,1,1);
			$qry="INSERT INTO grants (id_u,id_d,canwrite) VALUES (" . $this->admin_id . "," . $this->pwd_db_id . ",1)"; 
			if(!$this->query_command($qry))
				echo $this->lastErrorMsg();
			
			
			
		$qry="
			INSERT INTO qLite_qry(name, qry,adminqry,grp ) VALUES('User', 
			'
			INSERT INTO users(user,pwd,lev) VALUES(PUT_NAME_HERE,''e52cbe1aa2f5cf7b68225ad60eb9ba0d5bc376c5481764d70929eb3d65d00512'',0);
			',1,'insert');
			
			
			
			INSERT INTO qLite_qry(name,qry,adminqry,grp) 
			VALUES('Menu',
			'INSERT INTO menus(name,isif,cof,nameof, grp,db) 
			VALUES (''name'', is_input_function, ''html code or function name'', name of a existing function OR NULL, ''grpname'',''db'' )',1,'insert');
	
	
	
	
	INSERT INTO  qLite_qry(name, qry,adminqry,grp )  VALUES('DB', 
	'DELETE FROM dbs 
	WHERE upper(db)=upper(''dbname'');',1,'delete');
	
	INSERT INTO  qLite_qry(name, qry,adminqry,grp )  VALUES('User', 
		'DELETE FROM users 
		WHERE user=''name'';',1,'delete');
		
	INSERT INTO  qLite_qry(name, qry,adminqry,grp )  VALUES('Menu', 
		'DELETE FROM menus
		WHERE  id=id_menu;',1,'delete');
		
	INSERT INTO  qLite_qry(name, qry,adminqry,grp )  VALUES('User Id', 
	'SELECT id,user 
	FROM users 
	WHERE upper(user)=upper(PUT_NAME_USER_HERE);',1,'select');
	
	INSERT INTO qLite_qry(name,qry,adminqry,grp) 
	VALUES('All Auths','SELECT u.user,d.db, g.canwrite 
		FROM users u 
		INNER JOIN grants g ON  u.id=g.id_u 
		INNER JOIN dbs d ON d.id =g.id_d 
		ORDER BY user ',1,'select');
	
	INSERT INTO qLite_qry(name,qry,adminqry,grp) 
		VALUES('Menu Grants',
		'SELECT m.id \"Menu Id\", coalesce(u.id,''(NULL)'') \"User Id\", coalesce(d.id,''(NULL)'') \"DB Id\",  m.name, m.grp \"menuGroup\",
		m.db \"menuDb\",coalesce(d.db,''(ALLS)'') database,coalesce(u.user,''(ALLS)'') user
		FROM mtodu mu 
		INNER JOIN menus m ON mu.id_m=m.id	
		LEFT JOIN  dbs d ON mu.id_d=d.id
		LEFT JOIN users u ON mu.id_u=u.id
		WHERE true',0, 'select');
		
		INSERT INTO qLite_qry(name,qry,adminqry,grp) 
		VALUES('Menus',
				'SELECT id,name,grp, db, description, isif,CASE WHEN length(cof) >100 THEN substr(cof,1,100) || ''...'' ELSE cof END cof ,nameof
				FROM menus
				WHERE true;',0, 'select');
	
	INSERT INTO qLite_qry(name, qry,adminqry,grp )  VALUES('Reset User Pwd', 
		'UPDATE users SET pwd=''e52cbe1aa2f5cf7b68225ad60eb9ba0d5bc376c5481764d70929eb3d65d00512'' 
		WHERE id IN (SELECT id FROM users WHERE upper(user)=upper(PUT_NAME_USER_HERE));',1,'update');
		
	INSERT INTO qLite_qry(name, qry,adminqry,grp ) VALUES('Default Password',
		'UPDATE qLite_qry SET qry=''INSERT INTO users(user,pwd) 
		VALUES(''''nome'''',''''e52cbe1aa2f5cf7b68225ad60eb9ba0d5bc376c5481764d70929eb3d65d00512'''')'' 
		WHERE name=''User'' AND grp=''insert'';
		UPDATE qLite_qry SET qry=
		''UPDATE qLite_qry SET qry=''''
			UPDATE users
			SET pwd=''''''''e52cbe1aa2f5cf7b68225ad60eb9ba0d5bc376c5481764d70929eb3d65d00512'''''''' 
			WHERE id IN (SELECT id FROM users WHERE upper(user)=upper(PUT_NAME_USER_HERE));''''''
	    WHERE name=''Reset User Pwd'' AND grp=''update'';',1,'update');
		
	INSERT INTO qLite_qry(name,qry,adminqry,grp) 
	VALUES('Database Name','UPDATE dbs SET db=''main'' ,grp='''' WHERE db=''qLite.sqlite3''',1,'update');
		
		
		INSERT INTO qLite_qry(name,qry,adminqry,grp) 
		VALUES('Grant Menu',
		'/* Global menu: Menu valid over all for all users
		INSERT INTO mtodu(id_m,id_u,id_d)
		SELECT m.id, NULL,NULL
		FROM menus m
		WHERE m.id IN (mid, ... PUT_MENU_id_LIST);
		*/
		
		/* Menu Valid only inside a database, for all granted  users 
		INSERT INTO mtodu(id_m, id_u, id_d)
		SELECT m.id, NULL,d.id
		FROM menus m,dbs d
		WHERE m.id IN (mid, ... PUT_MENU_id_LIST)
		AND upper(d.db) IN (upper(''dbname'') ...PUT_DB_LIST);
		*/
		
		/* Menu Valid over all, only for the specified users   
		INSERT INTO mtodu(id_m,id_u,id_d)
		SELECT m.id, u.id,NULL
		FROM menus m,users u
		WHERE m.id IN (mid, ... PUT_MENU_id_LIST)
		AND upper(u.user) IN (upper(''username'') ...PUT_USER_LIST);
		*/
		
		/* Menu Valid only inside a database for only specified users    
		INSERT INTO mtodu(id_m,id_u,id_d)
		SELECT m.id, u.id, d.id
		FROM menus m,users u,dbs d
		WHERE m.id IN (mid, ... PUT_MENU_id_LIST)
		AND upper(u.user) IN (upper(''username'') ...PUT_USER_LIST)
		AND upper(d.db) IN (upper(''dbname'') ...PUT_DB_LIST);
		*/;',1,'grant');
		
		
		INSERT INTO qLite_qry(name,qry,adminqry,grp) 
		VALUES('Promote User To',
		'/* Inherits FROM a specified user overall rights
		INSERT INTO mtodu(id_m,id_u,id_d)
		SELECT mu.id_m, u2.id,NULL  
		FROM  mtodu mu
		INNER JOIN users u ON mu.id_u=u.id
		LEFT JOIN users u2 ON mu.id_u=u2.id AND upper(u2.user)=upper(PUT_CHILD_HERE)
		WHERE  u2.id IS NULL AND upper(u.user)=upper(PUT_PARENT_HERE)
		*/
		
		/* Inherits FROM a specified user the rights inside a specified db
		INSERT INTO mtodu(id_m,id_u,id_d)
		SELECT mu.id_m, u2.id,d.id  
		FROM  mtodu mu
		INNER JOIN users u ON mu.id_u=u.id
		INNER JOIN dbs d ON mu.id_d=d.id 
		LEFT JOIN users u2 ON mu.id_u=u2.id AND upper(u2.user)=upper(PUT_CHILD_HERE)
		WHERE  u2.id IS NULL AND upper(u.user)=upper(PUT_PARENT_HERE)
		AND upper(d.db)=upper(PUT_DB_HERE) 
		*/',1,'grant');
		
		
		INSERT INTO  qLite_qry(name, qry,adminqry,grp )  VALUES('Revoke User', 
		'DELETE FROM grants 
		WHERE id_u=? AND id_d =?;',1,'grant');
		
		INSERT INTO  qLite_qry(name, qry,adminqry,grp )  VALUES('Revoke Menu', 
		'DELETE FROM mtodu 
		WHERE id_m = ? AND id_u=? AND id_d =?;',1,'grant');
		
	
		INSERT INTO qLite_qry(name, qry,adminqry,grp )  VALUES('Grant User To db', 
		'
			INSERT INTO grants(id_u,id_d,canwrite) 
			SELECT u.id,d.id,0 
			FROM users u, dbs d 
			WHERE upper(u.user)=upper(PUT_USER_NAME_HERE) AND upper(d.db)=upper(PUT_DB_NAME_HERE);
		',1,'grant');
		
		
	INSERT INTO menus (uid,dateid,name,isif,cof,nameof,grp)VALUES 
	(1000000001,'2024/03/15','New Database',0,
		'<table id=\"menutbl\" class =\"tblfrm tlbmenu\" >
		<tr><td>Database Path</td><td><input type=\"text\"  name=\"new_db\" value=\"\"></td></tr>
		<tr><td colspan=\"2\"><input type=\"submit\"  name=\"qry_exec\" value=\"Ok\"></td></tr>
	</table>',NULL,'Admin');
	
	INSERT INTO mtodu(id_m,id_u,id_d)
	SELECT last_insert_rowid(), u.id, d.id
	FROM users u, dbs d 
	WHERE upper(u.user)= upper('". $_SESSION["QLITE_ADMIN_NAME"] . "')
	AND upper(d.db)=upper('". $_SESSION["QLITE_PWD_DB_NAME"] . "');
	
	
	INSERT INTO menus (uid,dateid,name,isif,cof,nameof,grp) VALUES (1000000002,'2024/03/15','Concatenate',0,'<script>  
	function on_doc_load() 
	  { 
	  
			var styleSheet = document.createElement(\"style\");
			styleSheet.innerHTML=`.selCell{background-color:#99EDE2;}`;
			document.body.appendChild(styleSheet);
	} 
	  
	  function on_tbl_clk(r,c) 
	  { 
		var tbls=document.getElementsByClassName(`anstbl`);
		if(tbls.length<1)return ;
		var tbl=tbls[0];
		var to_t= document.getElementById(`rd_text`).checked;
		var srv=document.getElementById(`service`);
		var to_up=document.getElementById(`cb_upper`).checked;
		if(to_t)
			srv.value= srv.innerHTML=srv.value +(srv.value.length>0?`, `:``) + (to_up?`upper(`:``) + `''` + 
			tbl.rows[r].cells[c].innerHTML.replaceAll(`''`,`''''`).replaceAll(`>`,`>`).replaceAll(`<`,`<`).replaceAll(`<br>`,``) +`''`+ (to_up?`)`:``);
		else
			srv.value= srv.innerHTML=srv.value +(srv.value.length>0?`, `:``) + tbl.rows[r].cells[c].innerHTML;
		tbl.rows[r].cells[c].classList.add(`selCell`);
	} 
	function on_rd_change()
	{
	 
		document.getElementById(`cb_upper`).disabled=! document.getElementById(`rd_text`).checked;
		
	}
	</script>
		<table id=\"menutbl\" class =\"tblfrm tlbmenu\" >
		<tr><td><input type=\"radio\"  id=\"rd_text\" name=\"cat\" value=\"text\" onchange=\"on_rd_change()\" checked=\"true\"><label for=\"radio_text\">Text</label></td><td>
		<input type=\"checkbox\"  id=\"cb_upper\" name=\"cb_upper\" checked=\"true\"><label for=\"cb_upper\">upper()</label></td></tr>
		<tr><td colspan=\"2\"><input type=\"radio\"  onchange=\"on_rd_change()\"   name=\"cat\" value=\"number\"  ><label for=\"number\">Number</label></td></tr>
	</table>
	<span style =\"text-align:\">Click on the cells of a table,it will<br>
	concatenate them  in  a list to use in a IN (...element list...)<br>
	If Text is selected, the value will be wrapped inside the  '''' '''', and escaped. 
	Number  will be formated as number.</span>',NULL,'Tools.String');
	
	INSERT INTO mtodu(id_m,id_u,id_d)
	SELECT last_insert_rowid(), NULL, NULL
	UNION 
	SELECT last_insert_rowid(), u.id, NULL
	FROM users u
	WHERE u.user='public';
	
	
	INSERT INTO menus (uid, dateid,name,isif,cof,nameof,grp)
			VALUES (1000000003,'2024/03/15','Compute SHA',0,'
			<script>
				function get_hash()
			{
			  document.getElementById(`service`).style.setProperty(`display`, `block`);
			  var xhr = new XMLHttpRequest();
			  var sha = document.getElementById(`sha`).value;
			  var par=`ajax=1&asha=` + sha;
			  xhr.open(`POST`, `qLite.php`,true);
			    xhr.setRequestHeader(`Content-Type`, `application/x-www-form-urlencoded; charset=UTF-8`);
			  xhr.onreadystatechange = function () {
				var DONE = 4; // readyState 4 significa che la richiesta è stata eseguita.
				var OK = 200; // lo stato 200 è un ritorno riuscito. 
				if (xhr.readyState === DONE) {
				  if (xhr.status === OK) {
					document.getElementById(`service`).value = xhr.responseText;
					document.getElementById(`service`).innerHTMLe = xhr.responseText;
				  } else {
					console.log(`Error: ` + xhr.status); // Si è verificato un errore durante la richiesta.
				  }
				}
			  };
			  xhr.
			send(par);
		 }
	  
	  </script>
			<table id=\"menutbl\" class =\"tblfrm tlbmenu\" >
		<tr><td>sha-256</td><td><input type=\"password\"  autocomplete=\"new-password\" id=\"sha\" name=\"sha\" value=\"\"></td></tr>
		<tr><td colspan=\"2\"><button   type=\"button\" onclick=\"javascript:get_hash();\">Ok</button></td></tr>
	</table>',NULL,'Tools');
	
	INSERT INTO mtodu(id_m,id_u,id_d)
	SELECT last_insert_rowid(), u.id, NULL
	FROM users u 
	WHERE upper(u.user)= upper('". $_SESSION["QLITE_ADMIN_NAME"] . "');

	
		INSERT INTO menus (uid,dateid,name,isif,cof,nameof,grp)
			VALUES (1000000004,'2024/03/15','Escape',0,
			'
<script>
		function esc_escape() 
		{
			var rd=document.getElementsByName(\"esc\");
			if(rd[0].checked)
			{
				/*SQL*/
				document.getElementById(`service`).innerHTML=
				document.getElementById(`service`).value= 
				`''` +document.getElementById(`gettxt`).value.replaceAll(`''`,`''''`) + `''`;
			}else if(rd[1].checked)
			{
				/*php*/
				document.getElementById(`service`).innerHTML=
				document.getElementById(`service`).value= 
				`\"` +document.getElementById(`gettxt`).value.replaceAll(`\"`,`\\\\\"`) + `\"` ;
			}else if(rd[2].checked)
			{
					/*c*/
				document.getElementById(`service`).innerHTML=
				document.getElementById(`service`).value= 
				`\"` +document.getElementById(`gettxt`).value.replaceAll(`\"`,`\\\\\"`).replaceAll(`\n`,`\\\\n\"\n\"`)+ `\"` ;
			}else if(rd[3].checked)
			{
				/*js*/
				document.getElementById(`service`).innerHTML=
				document.getElementById(`service`).value= 
				\"`\" +document.getElementById(`gettxt`).value.replaceAll(\"`\",\"\\\\`\")+ \"`\" ;
				
			}else if(rd[4].checked)
			{
				/*html*/
				document.getElementById(`service`).innerHTML=
				document.getElementById(`service`).value= 
				document.getElementById(`gettxt`).value.replaceAll(`&`,`&amp;`).replaceAll(`<`,`&lt;`).replaceAll(`>`,`&gt;`).
				replaceAll(`\n`,`<br>\n`)
			}
		} 
	</script>
	<table id=\"menutbl\" class =\"tblfrm tlbmenu\" >
		<tr><td><input type=\"radio\" id=\"sql\" 	name=\"esc\" value=\"sql\" checked=\"true\"><label for=\"sql\">To SQL</label></td></tr>
		<tr><td><input type=\"radio\" id=\"php\" 	name=\"esc\" value=\"php\"><label for=\"php\">To PHP</label></td></tr>
		<tr><td><input type=\"radio\"  id=\"c\"		 name=\"esc\" value=\"c\"><label for=\"c\">To C</label></td></tr>
		<tr><td><input type=\"radio\" id=\"js\"		 name=\"esc\" value=\"js\"><label for=\"js\">To JavaScript</label></td></tr>
		<tr><td><input type=\"radio\" id=\"html\" name=\"esc\" value=\"html\"><label for=\"html\">To HTML(no Javascript)</label></td></tr>
		<tr><td>Put text to escape here</td></tr>
		<tr><td><textarea id=\"gettxt\" rows=\"8\" cols=\"30\"></textarea></td></tr>
		<tr><td><button  type=\"button\"  onclick=\"javascript:esc_escape();\">Escape</button></td></tr>
	</table>
	<span style =\"text-align:\">This menu escapes text accordling to the chosen format.<br> It  is not yet fully implemented and contains just the basic charachters to allow me to develope the rest of the program.</span> 			
	',NULL,'Tools.String');
	
	INSERT INTO mtodu(id_m,id_u,id_d)
	SELECT last_insert_rowid(), NULL, NULL
	UNION 
	SELECT last_insert_rowid(), u.id, NULL
	FROM users u
	WHERE u.user='public';
	
	
	INSERT INTO menus (uid,dateid,name,isif,cof,nameof,grp)
			VALUES (1000000005,'2024/03/15','Menu Builder',0,'<span style=\"position:relative; top:0%; left:0%; \"> Get query<br></span>
	<button  type=\"button\"  onclick=\"javascript:set_val(`str`,`SELECT id,name, isif,cof,nameof,grp FROM menus WHERE true;`);\">Get Query</button> 
	<button   type=\"button\" onclick=\"javascript:document.getElementById(`help`).style.setProperty(`display`,`block`);\"> Help</button>
	
	<span style =\"text-align: left; display:none;\" id=\"help\">Clic on Get Query<br>Set the filters in the query and run it<br>Modify the code in the input box<br>
	Chose if update  the current item or insert a new one. <br> Run the resultant query<br> 
	<script> 
		var _row=-1;
		var old_color;
		function up_script()
		{
			if(_row<1) return;
			var ta=document.getElementById(\"service\").value;
			var r=document.getElementsByClassName(\"anstbl\")[0].rows[_row].cells[0].innerHTML;
			document.getElementById(\"str\").value=
			`UPDATE menus SET cof=''` + ta.replaceAll(\"''\",\"''''\") +
			`'',name=name, isif=isif,nameof=nameof,grp=grp,db=db, description=description WHERE id=` + r +\";\"; 			
		}
		function ins_script()
		{
			if(_row<1) return;
			var ta=document.getElementById(\"service\").value;
				document.getElementById(\"str\").value=
			`INSERT INTO menus (cof,name,isif,nameof,grp,db,description)
			VALUES (''` + ta.replaceAll(\"''\",\"''''\") + `'',NAME,isif,NULL,GROUP,DB,DESCRIPTION);`
		}
	 
		function on_doc_load() 
		{ 
			if(document.getElementsByClassName(\"idnameisifcofnameofgrp\").length<1) return; 
			var cont=document.getElementById(\"serv_container\");
			var btup=document.createElement(\"button\");
			btup.innerHTML=\"Update Script\"; 
			btup.type=\"button\";
			btup.onclick=up_script;
			cont.appendChild(btup); 
			var btins=document.createElement(\"button\");
			btins.innerHTML=\"Insert Script\";  
			btins.onclick=ins_script;
			btins.type=\"button\";
			cont.appendChild(btins);
			
			var styleSheet = document.createElement(\"style\");
			styleSheet.innerHTML=\".idnameisifcofnameofgrp tr {color:#069;text-decoration: underline;cursor: pointer;} .selRow,.anstbl .selRow:nth-child(odd)  {background-color:#99EDE2;}\";
			document.body.appendChild(styleSheet);
			 
			document.getElementById(\"service\").classList.toggle(\"service\");
	 
		} 
	  
	  function on_tbl_clk(r,c) 
	  { 
	  
			var col=document.getElementsByClassName(\"idnameisifcofnameofgrp\");
			if(col.length<1 || r<1 ) return; 
			var tbl=col[0];
			if(r !=_row && _row>0)
			{
				tbl.rows[r].classList.toggle (\"selRow\");
				tbl.rows[_row].classList.toggle (\"selRow\");
			}else if(r!=_row)
					tbl.rows[r].classList.toggle (\"selRow\");
			document.getElementById(\"service\").innerHTML=tbl.rows[r].cells[3].innerHTML.replaceAll(\"<br>\",\"\\n\");
			_row=r;
			
	}
	</script>
  ',NULL,'Tools'); 
	
	INSERT INTO mtodu(id_m,id_u,id_d)
	SELECT last_insert_rowid(), u.id, d.id
	FROM users u, dbs d 
	WHERE upper(u.user)= upper('". $_SESSION["QLITE_ADMIN_NAME"] . "')
	AND upper(d.db)=upper('". $_SESSION["QLITE_PWD_DB_NAME"] . "');
	
	INSERT INTO menus (uid,dateid,name,isif,cof,nameof,grp)VALUES (1000000005,'2024/03/15',
	'Wp',0,'<label for=\"wp_file\">WP File</label><input type=\"text\"  name=\"wp_file\" value=\"\">
	<input type =\"submit\"  name=\"Ok\" value=\"Ok\">',NULL,'Admin');
	
	
	INSERT INTO menus (uid,dateid,name,isif,cof,nameof,grp) VALUES (1000000006,'2024/03/15',
			'SQL File',0,'
		<script>
		function on_rd_change()
		{
			var d=document.getElementById(\"rd_fu\").checked;
			document.getElementById(\"i_u\").disabled=!d;
			document.getElementById(\"i_l\").disabled=d;
		}
		</script>
		<table id=\"menutbl\" class =\"tblfrm tlbmenu\" >
		<tr><td><input type=\"radio\" id=\"rd_fu\" name=\"rd_f\" onchange=\"on_rd_change()\" value=\"up\" checked> <label for=\"rd_fu\">Upload File</label></td><td>
		<input type=\"file\" id=\"i_u\" name=\"up_file\" value=\"\"></td></tr>
		<tr><td><input type=\"radio\" id=\"rd_fl\" name=\"rd_f\" onchange=\"on_rd_change()\" value=\"loc\" > <label for=\"rd_fl\">Local File</label></td><td> <input type=\"text\"  name=\"loc_file\"  id=\"i_l\" value=\"\" disabled ></td></tr>
		<tr><td colspan=\"2\"> <input type =\"submit\"  name=\"bt_up_f\" value=\"Ok\"></td></tr>
		</table>',NULL,'Tools');
	
	INSERT INTO mtodu(id_m,id_u,id_d)
	SELECT last_insert_rowid(), u.id, NULL
	FROM users u 
	WHERE upper(u.user)= upper('". $_SESSION["QLITE_ADMIN_NAME"] . "');
	
	INSERT INTO menus (uid,dateid,name,isif,cof,nameof,grp)
			VALUES (1000000007,'2024/03/15','Access As',0,'<label for=\"access_as\" style=\"padding:0 5px 0 0;\">Access as User:</label><input type=\"text\"  name=\"access_as\" value=\"\">
			<input type =\"submit\"  name=\"bt_access_as\" value=\"Ok\">',NULL,'Admin');
	
	INSERT INTO mtodu(id_m,id_u,id_d)
	SELECT last_insert_rowid(), u.id, NULL
	FROM users u 
	WHERE upper(u.user)= upper('". $_SESSION["QLITE_ADMIN_NAME"] . "');
	
	
	INSERT INTO menus (uid,dateid,name,isif,cof,nameof,grp) VALUES (1000000008,'2024/03/15',
'Save Query',0,'	<span style=\"position:relative; top:0%; left:0%; \"> Get query<br></span>
	<button  type=\"button\"  onclick=\"javascript:set_val(`str`,`SELECT id,grp,name,qry,adminqry FROM qLite_qry WHERE true;`);\">Get Query</button> 
	<button   type=\"button\" onclick=\"javascript:document.getElementById(`help`).style.setProperty(`display`,`block`);\"> Help</button>
	
	<span style =\"text-align: left; display:none;\" id=\"help\">If you have to modify an existing query, Click on Get Query<br>Set the filters in the query and run it<br> Modify the code in the boxes here down and then click on update<br> If you have to insert a new one just fill the boxes and click on insert.<br></span>
	<table id=\"menutbl\" class =\"tblfrm tlbmenu\" >
		<tr><td>Name </td><td><input type=\"text\" id=\"s1\" name=\"s1\" value=\"\"></td></tr>
		<tr><td>Group</td><td><input type=\"text\" id=\"s2\"  name=\"s2\" value=\"\"></td></tr>
		<tr><td>Admin Query</td><td><input type=\"text\"  id=\"n1\" name=\"n1\" value=\"\"></td></tr>
		<tr><td>Query</td><td><textarea name=\"s3\"  id=\"s3\" rows=\"8\" cols=\"30\"></textarea></td></tr>
		<tr><td colspan=\"2\"><input type=\"submit\" name=\"b1\" value=\"Insert\"><span> </span>
		<input type=\"submit\" name=\"b2\" value=\"Update\"><span> </span>
		<button type=\"button\" onclick=\"on_del()\" >Delete</button> </td>
		</tr> <span> </span>
	</table>
	<input type=\"hidden\" name=\"qry1\" value=\"INSERT INTO qLite_qry(name,grp,adminqry,qry) VALUES ({s1},{s2},{n1},{s3});\">
	<input type=\"hidden\" name=\"qry2\" value=\"UPDATE qLite_qry SET name={s1},grp={s2},adminqry={n1},qry={s3} WHERE id={n2}\">
	<input type=\"hidden\" name=\"qry3\" value=\"DELETE FROM qLite_qry WHERE id={n2}\">
	<input type=\"hidden\" id=\"n2\"  name=\"n2\" value=\"\">
	
	<script> 
		var _row=-1;
		function on_del()
		{
			if(confirm(`Do you want delete the current query?`))
			{
				force_submit(`main_frm`,`b3`,`dummy`);
			}
		}
		function on_doc_load() 
		{ 
			if(document.getElementsByClassName(\"idgrpnameqryadminqry\").length<1) return; 
			var styleSheet = document.createElement(\"style\");
			styleSheet.innerHTML=\".idgrpnameqryadminqry tr {color:#069;text-decoration: underline;cursor: pointer;} .selRow,.anstbl .selRow:nth-child(odd)  {background-color:#99EDE2;}\";
			document.body.appendChild(styleSheet);
		} 
	  
	  function on_tbl_clk(r,c) 
	  { 
		var col=document.getElementsByClassName(\"idgrpnameqryadminqry\");
		if(col.length<1 || r<1 ) return; 
		var tbl=col[0];
		if(r !=_row && _row>0)
		{
			tbl.rows[r].classList.toggle (\"selRow\");
			tbl.rows[_row].classList.toggle (\"selRow\");
		}else if(r!=_row)
				tbl.rows[r].classList.toggle (\"selRow\");
		//id,grp, name,qry,adminqry,
		document.getElementById(\"s1\").value=tbl.rows[r].cells[2].innerHTML;
		document.getElementById(\"s2\").value=tbl.rows[r].cells[1].innerHTML;
		document.getElementById(\"n1\").value=tbl.rows[r].cells[4].innerHTML;
		document.getElementById(\"s3\").value=tbl.rows[r].cells[3].innerHTML;
		document.getElementById(\"n2\").value=tbl.rows[r].cells[0].innerHTML;
		_row=r;
	}
	</script>',NULL,'Tools');
	
	INSERT INTO mtodu(id_m,id_u,id_d)
	SELECT last_insert_rowid(), u.id, NULL
	FROM users u 
	WHERE upper(u.user)= upper('". $_SESSION["QLITE_ADMIN_NAME"] . "');
	
	UPDATE qLite_qry SET qry=replace(qry,'	','');
	";
		
		$this->query_command($qry);
		
		}
	}
		public function get_last_error()
		{
				return $this->last_err;
		}
		public function get_user_id($user)
		{
			$qry="SELECT id FROM users WHERE user='" . str_replace("'","''",$user) ."'";
			$r=$this->query_arr($qry);
			if($r && is_array($r))
			{
				$this->free();
				return $r[0];
			}				
			return NULL;
			
		}
		public function get_db_id($db )
		{
			$qry="SELECT id FROM dbs WHERE db='" . str_replace("'","''",$db) ."'";;
			$r=$this->query_arr($qry);
			if($r && is_array($r))
			{
				$this->free();
				return $r[0];
			}				
			return NULL;
			
		}
		public function get_db_path_by_name($db )
		{
			$qry="SELECT path FROM dbs WHERE db='" . str_replace("'","''",$db);
			$r=$this->query_arr($qry);
			if($r && is_array($r))
			{
				$this->free();
				return $r[0];
			}				
			return NULL;
			
		}
		public function get_db_path_by_id($id )
		{
			$qry="SELECT path FROM dbs WHERE id=" . $id;
			$r=$this->query_arr($qry);
			if($r && is_array($r))
			{
				$this->free();
				return $r[0];
			}				
			return NULL;
			
		}
		public function insert_db($db_path)
		{
			$res= new dbsqlite3($db_path,SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
			if($res->is_valid_db())
				$res->build($this,$db_path);
			else 
				return NULL;	
			return $res;
			
		}
		public function get_db($db_name,$user, $mode=SQLITE3_OPEN_READONLY )
		{
			$this->last_err="";
			if(!$db_name || !$user )
				return NULL;
			
			$qry="	SELECT g.canwrite, d.path   
					FROM users  u 
					INNER JOIN grants g ON u.id=g.id_u 
					INNER JOIN dbs d ON d.id=g.id_d 
					WHERE upper(d.db)=upper('" .str_replace("'","''",$db_name) . "') 
						AND  upper(u.user)=upper('" . str_replace("'","''",$user) . "')"; 
			
			$r=$this->query_arr($qry);
			if(!$r)
			{	
				$this->last_err=ERR_WRONG_DB;
				return NULL;
			} else if(!is_array($r))
			{
				$this->last_err=$r;
				return NULL;
			}
			$user_can_write=$r[0]==0?false:true;
			if ($mode==SQLITE3_OPEN_READONLY)
				$mode= $r[0]==0?SQLITE3_OPEN_READONLY:SQLITE3_OPEN_READWRITE;
			$res= new dbsqlite3($r[1],$mode );
			$this->free();
			return $res;
		}
		public function get_db_noaut($db_name)
		{
			$qry="	SELECT d.path   
					FROM dbs d 
					WHERE upper(d.db)=upper('" .str_replace("'","''",$db_name) . "')";
		 
			$r=$this->query_arr($qry);
			if(is_array($r))
			{
				$res= new dbsqlite3($r["path"],SQLITE3_OPEN_READWRITE );
				$this->free();
				return $res;
			}
			return NULL;
		}
		
		public function get_db_aut($db_name,$user,$pwd)
		{

			
				$qry="SELECT id FROM users WHERE upper(user)=upper('" . str_replace("'","''",$user) . "') AND pwd='" .
				hash(QLITE_HASH_ALG, $pwd) ."'";
			
			$r=$this->query_arr($qry);
			 if(!is_array($r))
			{
				$this->last_err=ERR_WRONG_PWD;
			 
				return NULL;
			}
			$this->free();
			return $this->get_db($db_name,$user);
			
		}
}
	
	
	$pwd_db=new pwd_db3($_SESSION["QLITE_PWD_DB"]);
	
	if(isset($_POST["bt_access_as"])&& strlen($_POST["access_as"])>0  )
	{
		$s3tmp=$pwd_db->get_db($db_name,$_POST["access_as"]);
		if($s3tmp)
		{
			$_SESSION["QLITE_AUT_USER"]=$_POST["access_as"];
			$s3tmp->close();
			if(isset($_POST["menu"]))
				unset($_POST["menu"]);
		}
		else 
			$main_errmsg=ERR_WRONG_DB;
	}
	if (isset($_POST["ajax"]))
	{
		
		header ( 'Content-Type: text/plain' );
		if(isset($_POST["asha"]))
		{
			echo hash(QLITE_HASH_ALG, $_POST["asha"]); 
		}
		else if( isset($_POST["aqry"])    )  
		{
			
			$adb_name=isset($_POST["adb"])?$_POST["adb"]:$_SESSION["QLITE_CUR_DB"];
		 
			$ajdb=$pwd_db->get_db_noaut($adb_name);
			if($ajdb && $ajdb->is_valid_db())
			{
			echo $ajdb->arr_to_table( $_POST["aqry"],$vl);
			$ajdb->close();
			}
			else
			echo '';
	 	}
		
		exit();
	}
	else echo '';
	
	
	$s3 =NULL;
	
	if($_SESSION["QLITE_PWD_REQUIRED"]==false)
	{
		//no pwd
		$state=STATE_WORK;
		$_SESSION["QLITE_AUT_USER"]=$_SESSION["QLITE_ADMIN_NAME"];
		if($db_name==NULL)
		{
			$s3=$pwd_db;
			$_SESSION["QLITE_CUR_DB"]=$db_name=$_SESSION["QLITE_PWD_DB_NAME"];
		}
		else 
		{
			$s3=$pwd_db->get_db($db_name,$_SESSION["QLITE_AUT_USER"],SQLITE3_OPEN_READWRITE );
			if(!$s3->is_valid_db())
			{
				$s3=$pwd_db;
				$_SESSION["QLITE_CUR_DB"]=$db_name=$_SESSION["QLITE_PWD_DB_NAME"];
			}
				
		}
		
		$user_can_write=true;
	}
	else if(isset($_POST['state']) && strcmp($_POST['state'],"CHANGE_PWD")==0 && isset( $_POST["change_state"]) && 
	strcmp( LBL_LOGOUT,$_POST["change_state"] )==0)
	{
		//logout
		unset($_SESSION["QLITE_AUT_USER"]);
		unset($_SESSION["QLITE_CUR_DB"]);
		$state=STATE_LOGIN;
		
	}else if(isset($_POST['state']) && strcmp($_POST['state'],"CHANGE_PWD")==0 && isset( $_POST["change_state"]) &&
		strcmp($_POST["change_state"], LBL_CHANGE_PWD )==0)
	{
				//change pwd
				$state=STATE_CHANGE_PWD;
	}		
	else if((isset($_SESSION["QLITE_AUT_USER"]) && strcmp($_SESSION["QLITE_AUT_USER"],$_SESSION["QLITE_ADMIN_NAME"])==0)
		|| ( isset($_POST["user"]) && strcmp($_POST["user"],$_SESSION["QLITE_ADMIN_NAME"])==0 &&    
			strcmp(hash(QLITE_HASH_ALG,$_POST["pwd"]),QLITE_PWD_PWD)==0))
		{
				//admin login
			
				$_SESSION["QLITE_AUT_USER"]=$_SESSION["QLITE_ADMIN_NAME"];
				$state=STATE_WORK;
				$user_can_write=true;
				if($db_name==NULL)
				{
					$s3=$pwd_db;
					$_SESSION["QLITE_CUR_DB"]=$db_name=$_SESSION["QLITE_PWD_DB_NAME"];
				}
				else 
				{

					$s3=$pwd_db->get_db($db_name,$_SESSION["QLITE_AUT_USER"],SQLITE3_OPEN_READWRITE );
					if(!$s3 || !$s3->is_valid_db())
					{
						$s3=$pwd_db;
						$_SESSION["QLITE_CUR_DB"]=$db_name=$_SESSION["QLITE_PWD_DB_NAME"];
					}
					else 
						$_SESSION["QLITE_CUR_DB"]=$db_name;
				}
				
				if(!is_object($s3))
				{
					$main_errmsg="ERR: OPEN FILE PROBLEMS";
					$state=STATE_LOGIN;
				}
				
		} 
	else if(isset($_POST['state']) && strcmp($_POST['state'],"LOGIN")==0 
				&& isset($_POST["pwd"]) && $input_user )
	{
			//after login: check pwd  and set QLITE_AUT_USER
			$s3=$pwd_db->get_db_aut($db_name,$input_user,$_POST["pwd"]);
			
			if($s3==NULL)
			{ 
					if(isset($_SESSION["QLITE_AUT_USER"]))
						unset($_SESSION["QLITE_AUT_USER"]);
					if (isset($_SESSION["QLITE_CUR_DB"]))
						unset($_SESSION["QLITE_CUR_DB"]);
					$state=STATE_LOGIN;
					$main_errmsg=$pwd_db->get_last_error();
			} else
			{
				$state=STATE_WORK;
				$_SESSION["QLITE_AUT_USER"]=$input_user;
				$_SESSION["QLITE_CUR_DB"]=$db_name;
			}
	}else if(isset($_POST['state']) && strcmp($_POST['state'],"CHANGE_PWD")==0)
	{
			 //after change pwd: check pwd 
			$qry= "SELECT id FROM users WHERE upper(user)=upper('" . str_replace("'","''",$_SESSION["QLITE_AUT_USER"]) . "') AND pwd='" .
			hash(QLITE_HASH_ALG, $_POST["pwd"]) ."'";
			$r=$pwd_db->query_arr($qry);
			 
		if(is_array($r) )
		{	
			
			$qry="UPDATE users SET pwd='" . hash(QLITE_HASH_ALG, $_POST["new_pwd"]) . "' WHERE id=" . $r[0]; 
			echo $qry;
			$pwd_db->free();
			$t=$pwd_db->query_command($qry);
			if($t==true)

			{
				$main_log=LOG_PWD_CHANGED;	
				$s3=$pwd_db->get_db($_POST["db"],$_SESSION["QLITE_AUT_USER"]);
				if($s3==NULL)
				{
					if(isset($_SESSION["QLITE_AUT_USER"]))
						unset($_SESSION["QLITE_AUT_USER"]);
					if (isset($_SESSION["QLITE_CUR_DB"]))
						unset($_SESSION["QLITE_CUR_DB"]);
					$state=STATE_LOGIN;
					$main_errmsg=$pwd_db->get_last_error();
				}
				else
				{
					$state=STATE_WORK;
					$_SESSION["QLITE_CUR_DB"]=$db_name;
				}
			}
			else
			{
				if(isset($_SESSION["QLITE_AUT_USER"]))
						unset($_SESSION["QLITE_AUT_USER"]);
				if (isset($_SESSION["QLITE_CUR_DB"]))
						unset($_SESSION["QLITE_CUR_DB"]);
				$state=STATE_LOGIN;
				$main_errmsg=$pwd_db->get_last_error();
			}
			
		}
		else 
		{
			if(isset($_SESSION["QLITE_AUT_USER"]))
				unset($_SESSION["QLITE_AUT_USER"]);
			if (isset($_SESSION["QLITE_CUR_DB"]))
				unset($_SESSION["QLITE_CUR_DB"]);
					
			$state=STATE_LOGIN;
			$main_errmsg=ERR_WRONG_PWD;
		}
			
	}
	
	
	else if(isset($_SESSION["QLITE_AUT_USER"]) && strcmp($_SESSION["QLITE_AUT_USER"],$_SESSION["QLITE_ADMIN_NAME"])!=0)
	{			
				//get db logged user
				$s3=$pwd_db->get_db($db_name,$_SESSION["QLITE_AUT_USER"]);
				if($s3==NULL)
				{
					if(isset($_SESSION["QLITE_AUT_USER"]))
						unset($_SESSION["QLITE_AUT_USER"]);
					if (isset($_SESSION["QLITE_CUR_DB"]))
						unset($_SESSION["QLITE_CUR_DB"]);
					
					$state=STATE_LOGIN;
					$main_errmsg=$pwd_db->get_last_error();
					
				}
					else
				{
					$state=STATE_WORK;
					$_SESSION["QLITE_CUR_DB"]=$db_name;
				}
		
	}
	else if($input_user && strcmp($input_user,"public")==0)
	{			
				//public
				$_SESSION["QLITE_AUT_USER"]="public";
				$s3=$pwd_db->get_db($db_name,$_SESSION["QLITE_AUT_USER"]);
				if($s3==NULL)
				{
					if(isset($_SESSION["QLITE_AUT_USER"]))
						unset($_SESSION["QLITE_AUT_USER"]);
					if (isset($_SESSION["QLITE_CUR_DB"]))
						unset($_SESSION["QLITE_CUR_DB"]);
					
					$state=STATE_LOGIN;
					$main_errmsg=$pwd_db->get_last_error();
					
				}
				else
				{
					$state=STATE_WORK;
					$_SESSION["QLITE_CUR_DB"]=$db_name;
					
				}
	}
	else
	{
		if(isset($_SESSION["QLITE_AUT_USER"]))
			unset($_SESSION["QLITE_AUT_USER"]);
		if (isset($_SESSION["QLITE_CUR_DB"]))
			unset($_SESSION["QLITE_CUR_DB"]);
		$state=STATE_LOGIN;	
	}
	if ($state==STATE_WORK && $s3==NULL)
	{
		$state=STATE_UNKNOW;  
		$main_errmsg=ERR_OPSS . " linea:" . __LINE__;
	}else if( $state==STATE_WORK && $is_changed_db)
			$main_log=LOG_DB_CHANGED . $db_name; 
	
	
	function prepare_macro_qry($qry)
	{
		global $global_macros,$tags;
		foreach($global_macros as $v)
		{
			$qry=str_replace($v[0],"'" .str_replace("'","''", $v[1])."'",$qry);
			
		}
		 
		foreach ($tags as $t)
			if(isset($_POST[$t[0]]) && $t[2]=="s") //array("s1", 	"{s1}","s")
			{
				$qry=str_replace($t[1],"'" .str_replace("'","''", $_POST[$t[0]])."'",$qry);    

				}
			else if(isset($_POST[$t[0]]) && $t[2]=="n")
			{
				$qry=str_replace($t[1],$_POST[$t[0]],$qry);
			}
		return $qry;
	}
	
	$qry_res=NULL;
	
			
	if(isset($_SESSION["QLITE_AUT_USER"]))
	{
		
		$qry=isset($_POST["str"])?$_POST["str"]:(isset($_GET["qry"])?$_GET["qry"]:NULL);
		
		
		if(isset($_POST['new_db']) && strlen($_POST['new_db'])>0) 
		{
			
			$pwd_db->insert_db($_POST['new_db']);
				
			
		}
		else if ( isset($_POST['wp_file']) && strlen($_POST['wp_file'])>0 )
		{
			$time_start = microtime(true);
			if( $errmsg=$s3->load_file($_POST['wp_file']))
				echo $errmsg, '<br>';
			$time_end = microtime(true);
			$time = $time_end - $time_start;
			echo '(' . $time . ' Secondi) <br>';
		} else if(isset($_POST["qry_exec"]) && strcmp($_POST["qry_exec"],"Exec")==0 && strlen($qry)>0)
		{	
 
				$time_start = microtime(true);
				
				if( !is_string($r=$s3->query_command($qry)))
				{	
					 
					$time_end = microtime(true);
					$time = $time_end - $time_start;
					$qry_res='(' . $time . ' Secondi) <br>'.
					'(' . $s3->changes() . ' rows affected)';
				}
				else
					$qry_res=$r;
				
		}else if(isset($_POST["qry_exec"]) && strcmp($_POST["qry_exec"],"Query")==0 && strlen($qry)>0)
		{	
				
				
			 
				
				$time_start = microtime(true);
				$res=$s3->arr_to_table($qry,$vl);
				$time_end = microtime(true);
				$time = $time_end - $time_start;
				$qry_res= '(' . $time . ' Secondi) <br>'.
					$res;
		}else if(isset($_POST["bt_up_f"]))
		{
			
		 
			$f=NULL;
			if(strcmp($_POST["rd_f"],"up")==0 && isset($_FILES) &&  isset($_FILES['up_file']))
			{
				$f=fopen($_FILES['up_file']['tmp_name'],"rb");
				if($f)
					$l=filesize($_FILES['up_file']['tmp_name']);
			}
			else if(strcmp($_POST["rd_f"],"loc")==0 &&  strlen($_POST["loc_file"])>0)
			{
				$f=fopen($_POST["loc_file"],"rb");
				if($f)
					$l=filesize($_POST["loc_file"]);
			}
			if($f)
			{
				
				$qry=fread($f,$l);
				if($qry)
					$s3->query_command($qry);
				fclose($f);
			}
			
			
		}
		else  if(isset($_POST["menu"]))
		{	
	
			
			$qry=NULL;
			$mdb=isset($_POST[$wdb])?$_POST[$wdb]:$db_name;
			foreach($submits as $sb)
			{
				
				if(isset($_POST[$sb[0]])) //button 
				{
					
					$r=$pwd_db->query_arr("SELECT nameof FROM menus WHERE id=" . $_POST["id_menu"]);
					if(is_array($r) && !is_null($r["nameof"]) && strlen($r["nameof"])>0   )
					{
						 call_user_func($r["nameof"], $pwddb);
						 goto ESCAPE_NAMEOF;
					}
					
					if(isset($_POST[$sb[1]])) // qry
					{
						$qry=prepare_macro_qry($_POST[$sb[1]]);
					
						break;	
					}
				}
			
			}
			if($qry)
			{
					
				$s3w=$pwd_db->get_db_noaut($mdb,SQLITE3_OPEN_READWRITE);
				if($s3w)
				{
					$main_errmsg=$s3w->query_command($qry);
					if(!is_string($main_errmsg))
						$main_errmsg=NULL;
					$s3w->close();
				}
				else 
					$main_errmsg="ERR: Database not found";
		
			}
			if(isset($_POST["lqry"]))
			{
				$mdb=isset($_POST[$ldb])?$_POST[$ldb]:$db_name;
				$qry=prepare_macro_qry($_POST["lqry"]);
				$s3w=$pwd_db->get_db_noaut($mdb,SQLITE3_OPEN_READWRITE);
				if($s3w)
				{
					$main_errmsg=$s3w->query_command($qry);
					if(!is_string($main_errmsg))
						$main_errmsg=NULL;
					$s3w->close();
				}
				else 
					$main_errmsg="ERR: Database not found";
			}				
	
	}
			
	}
ESCAPE_NAMEOF:
	
	
	

	
	
echo '	<!DOCTYPE html>
	<html>
		<head>
			<title>qLite</title>
			<meta charset="UTF-8">
		 
				<style>
				#header
				{
					font-size:20px;
					padding:0px;
					margin:0px;
					background: linear-gradient(' ,CLR_STRONG_STRONG_GREY, ',', CLR_GREY,	 ');
				}
				
					.button_to_link {
						background: none;
						border: none;
						font-family: arial, sans-serif;
						color: #069;
						text-decoration: underline;
						cursor: pointer;
						padding:3px;
						}
					.headtbl {
						background: none;
						border-bottom:1px solid' , CLR_STRONG_GREY ,';
						
						font-family: arial, sans-serif;
						font-size: 12px;
						padding:0px;
						}
					.tblfrm{
						border-width:1px;
						background-color:' , CLR_GREY ,';
						border:2px solid' , CLR_STRONG_GREY ,	';
						font-size:12px; 
						 
						border-radius: 10px;
					}
					.menutbl table{
						border-width:0px;
						background-color:' , CLR_GREY ,';
						margin:0px;
						padding:0px;
						font-size:12px;}
					.anstbl td{
						border:1px solid ' , CLR_GREY ,';
						font-size: 12px;}
						
					 .anstbl table{
						max-width:100%;
						border:1px solid ' , CLR_STRONG_GREY ,';
						border-collapse: collapse;
						float:left;
					}
					.anstbl th{
						background-color: ' , CLR_STRONG_STRONG_GREY ,';
					}
					
					.anstbl tr:nth-child(odd){
						background-color: ' , CLR_STRONG_GREY ,';
					}
					 .hormenu  {	
						margin: 0;
						padding:2px;
						color: #069;
						cursor: pointer;
						list-style-type: none;
					 }
					.hormenu li { float:left; padding:2px;}
					 
					.hormenu  li ul{display:none;}
					
					.hormenu li:hover  
					{
						background-color:', CLR_STRONG_GREY ,';
					}
					.hormenu li:hover  ul 
					{
						display: block ;
						position: absolute;
						z-index:1;
						padding: 1px;
						margin: 0 0 0 -1px;
						border:1px solid ', CLR_STRONG_STRONG_GREY ,';
						background-color:', CLR_GREY ,';
					}
					
					.hormenu li:hover ul li{float:none; list-style-type: none;}
					
					.hm_sub_menu li{display: none;}
				
					.hm_sub_menu li ,.hm_sub_menu  ul {display: none;	list-style-type: none !important;}
					
					.hm_sub_menu:hover ul, .hm_sub_menu ul
					{	
						position:absolute;
						display:inline  !important;
						left:100%;
					}
					
					.hm_sub_menu:hover li
					{	
					 	position:relative;
						display: block ;
						z-index:1;
						padding: 1px;
					}
					
					
					 #vermenu_container{
						vertical-align:top;
						position:relative;
						}
					#vermenu {
							
						overflow: scroll;
						border: 1px solid ' , CLR_STRONG_GREY ,';
						background:#FFFFFF;
						border-radius: 10px;
						margin: 0;
						padding:2px;
						color:black;
						color: #069;
						cursor: pointer;
						list-style-type: none;
						position:absolute;
						top: 0;
						bottom: 0;
						left:0;
						right:0;
					}
		  
					 	
					 #vermenu li ul  
					{
						display:none;
						
					}
					#vermenu li:hover 
					{
							background-color:', CLR_GREY ,'
					}
			
					#vermenu li:hover ul 
					{	
						position:absolute;
						 
					 	z-index:auto !important; 
						margin:0 0 0 5px;
						padding:0px;
						display:  inline;
						border:1px solid ', CLR_STRONG_STRONG_GREY ,';
						background-color:', CLR_GREY ,';
						list-style-type:none;
					}
				 
					
					
					#vermenu li:hover li:hover
					{
						background-color: ', CLR_STRONG_STRONG_GREY ,';
					}
					.menu_container{
						vertical-align:top;
					}
					.qryerr
					{
					 
						background-color:', CLR_RED ,';
						border:1px solid ', CLR_STRONG_STRONG_GREY ,';
						padding:3px 10px 1px 10px;
					}
					.selected_menu {color:#ED1C24; padding:0; float:none; }
					textarea#service:empty{display:none; background:#C9EBC6;border-radius:10px; width:100%; }
					textarea#service{ background:#C9EBC6;border-radius:10px; width:100%; }
					textarea#str{position:relative; top:0%; left:0%; width:420px; height:180px; border-radius:10px;}


				</style>	
		</head>	
	<body onload="javascript:try{on_doc_load()}catch(e){}">
<h2  id="header">qLite <span style="font-size:10px;">powerd by Jurhas</span> </h2>  '
;	
	

		
	if(isset($_SESSION["QLITE_AUT_USER"]))
	{
			echo  '
			<form action="" method="post"> 
			<table class="headtbl" style="width:100%; background:',CLR_GREY ,'">
			<TR  ><TD style="float:left;" align="right"><b style="font-size:14px;">', $_SESSION["QLITE_AUT_USER"], '</b>
			<input type="hidden" name ="db" value="' , $db_name ,'">
			<input type="submit" class="button_to_link" style="font-size:10px;padding:1px;" name ="change_state" value="' , LBL_CHANGE_PWD ,'">
			<input type="submit" class="button_to_link" style="font-size:10px;padding:1px;" name ="change_state"  value="' , LBL_LOGOUT ,'">
			<input type="hidden" name="state" value="CHANGE_PWD">
			</td></tr>
			</table>
			<table>
			<TR><TD>On:<b style="font-size:16px;">', $db_name,'</b></td><td> 
			<input type="hidden" name ="db_changed" value="true">'; 	
			$qry="	SELECT db  FROM dbs d 
					INNER JOIN grants g ON d.id=g.id_d
					INNER JOIN users u ON u.id =g.id_u 
					WHERE u.user='" .str_replace("'","''",$_SESSION["QLITE_AUT_USER"]) ."' ORDER BY db"; 
			if( ($r= $pwd_db->query_arr($qry)) && is_array($r))
			{
				do{
					if(strcmp($r[0],$db_name)!=0)
						echo '<input type="submit"  class="button_to_link" name ="db" value="' , $r[0] , '">';
				}while ($r=$pwd_db->next());
			}	
			echo '</td></tr></table> </form>';
			
	}	
	
	if($main_errmsg)
	{
		echo '<div style="background-color:' , CLR_RED, '; font-size:16px; ">' . $main_errmsg . '</div>';
		$main_errmsg=NULL;
	}
	if(isset($main_log))
	{
		echo '<div style="background-color:' , CLR_GREEN, '; font-size:16px;">' . $main_log . '</div>' ;
		unset ($main_log);
	}
	if ($state==STATE_LOGIN)
		{
			echo '<form action="" method="post">
					<input type="hidden" id="state" name="state" value="LOGIN" />
					<input type="hidden" id="db_changed" name="db_changed" value="true" />
					<TABLE id="tblfrm" class="tblfrm" >
					<caption>Login</caption>
					<TR><TD>Database</TD>
						<TD><input type="text" name="db" value="', ($db_name==NULL?"":$db_name) ,'"  size="20"/></TD></TR>
					<TR><TD>User</TD><TD><input type="text" name="user" value="',
					(isset($_POST['user'])?$_POST['user']:'')    ,'"  size="20"/></TD></TR>
					<TR><TD>Password</TD><TD><input type="password" name="pwd" value=""  size="20"/></TD></TR>
					<TR style="border:none; background-color:default;"><TD><input type="submit" name="Ok" value="Ok"/></TD></TR>
					</TABLE>

					</FORM>';
			
		} else if ($state ==  STATE_CHANGE_PWD)
		{
				echo '<script>
					function is_pwd_valid(pwd){
							
							if (pwd.length<8)
								return 0;
							var isgood=0;
							for(var i=0;i< pwd.length && isgood <15;i++)
							{
								if( pwd[i]>="a" &&  pwd[i]<="z")
									isgood|=1;
								else if ( pwd[i]>="A" &&  pwd[i]<="Z")
									isgood|=2;
								else if 	( pwd[i]>="0" &&  pwd[i]<="9")
									isgood|=4;
								else 
									isgood|=8;
							}
							return isgood==15  ;
						}
					function on_new_pwd_change()
					{
						var new_pwd=document.getElementById("new_pwd").value;
						if(!is_pwd_valid(new_pwd) )
						{
							document.getElementById("td_new_pwd").style = "color:',CLR_STRONG_RED,';";
							document.getElementById("td_new_pwd").innerHTML="',LBL_NO_VALID_PWD,'"; 
						}
						else 
						{
							document.getElementById("td_new_pwd").style = "color:',CLR_STRONG_GREEN,'";
							document.getElementById("td_new_pwd").innerHTML="OK";
						}
					}
					function on_rip_pwd_change()
					{
						var new_pwd=document.getElementById("new_pwd").value;
						var repeat_pwd=document.getElementById("repeat_pwd").value;
						if( is_pwd_valid(new_pwd) && repeat_pwd==new_pwd )
						{
							document.getElementById("td_repeat_pwd").style = "color:',CLR_STRONG_GREEN,';";
							document.getElementById("td_repeat_pwd").innerHTML="OK";
							document.getElementById("bt_ok").disabled = false;
							 
						}
						else
						{
							document.getElementById("td_repeat_pwd").style = "color:',CLR_STRONG_RED,';";
							document.getElementById("td_repeat_pwd").innerHTML="',LBL_NO_REPEAT,'";
						}
					}
				</script>
				<form action="" method="post" >
					<fieldset>
					<INPUT type="hidden" id="state" name="state" value="CHANGE_PWD" />
					<INPUT type="hidden" id="db" name="db" value="' , 
					$db_name ,'" />
					<INPUT type="hidden" id="db" name="user" value="' , 
					$_SESSION['QLITE_AUT_USER'] , '" />
					<TABLE id="tblfrm" class="tblfrm" >
					<caption>Change Password	</caption>
					<TR><TD>',LBL_OLD_PWD, '</TD><TD><input type="password" name="pwd" value=""  size="20"/></TD><TD></TD></TR>
					<TR><TD>',LBL_NEW_PWD, '</TD><TD><input type="password" id="new_pwd" name="new_pwd" value=""  size="20"
					onfocusout="on_new_pwd_change()"  /></TD><TD id="td_new_pwd"></TD></TR>
					<TR><TD>',LBL_REPEAT_PWD, '</TD><TD><input type="password" id="repeat_pwd" name="repeat_pwd" value=""  size="20" onkeyup=" on_rip_pwd_change()" /></TD><TD id="td_repeat_pwd"></TD></TR>
					<TR><TD  style ="BORDER:NONE;"><input type="submit" id="bt_ok" name="bt_ok" value="Ok" disabled/>       <input type="submit" id="bt_back" name="bt_back" value="',LBL_BACK, '"/></TD></TR>
					</TABLE>
						</fieldset>
					</form>
					';
		} else if($state ==  STATE_WORK)		
		{
			$row_count =strcmp($_SESSION["QLITE_AUT_USER"],$_SESSION["QLITE_ADMIN_NAME"])==0?6
			: ($user_can_write?4:2);
			echo '
				<script>
						
						function set_val(id,val)
						{
							document.getElementById(id).innerHTML=document.getElementById(id).value=val;
						}
						function is_sql_identifier(key)
						{
							if (key.length==0) return false; 
							if( ((key[0]>=`a` && key[0]<=`z`)|| (key[0]>=`A` && key[0]<=`Z`) || key[0]==`_`))
							{
								for (var i=1;i<key.length;i++)
									if( !((key[0]>=`a` && key[0]<=`z`)|| (key[0]>=`A` && key[0]<=`Z`) || key[0]==`_` || 
								(key[0]>=`0` && key[0]<=`9`)) )
										return false;
								return true;
							}
							else
								return false;
							
						}
						function cat_sql_id(id,val)
						{
							var op_amp=(is_sql_identifier(val)?"":"\"");
							document.getElementById(id).value=document.getElementById(id).value +` ` + op_amp + val +op_amp;
							 event.stopPropagation() 
						}		
						function escape_qry()
						{ 
						
							var ord= document.getElementById("escape").value;
							document.getElementById("service").innerHTML="\'" +  ord.replaceAll("\'","\'\'").replaceAll("<","&lt;").replaceAll(">","&gt;") +"\'";
						}
						function force_submit(frm,key,val)
						{
							var input;
							if(input=document.getElementById(key))
							{
								input.value=val;
							}else
							{
								input= document.createElement("input");
								input.name= key;
								input.type="hidden";
								input.value=val;
								document.getElementById(frm).appendChild(input);
							}
							document.getElementById(frm).submit();
						}
					</script>
				 
		<FORM action="" method="post"  id="main_frm" enctype="multipart/form-data"  >
			
		<INPUT type="hidden" id="state" name="state" value="QRY"/>
		<INPUT type="hidden"  name="db" value="' , $db_name ,'"/>
		<table  id="tblfrm" class="tblfrm" >
		<tbody>
		<TR style="height:1px;"><TD><span style="padding-right:5px">', LBL_MAX_ROWS_TO_SHOW ,'</span><input type="number"  name="view_limit" value="', isset($_POST['view_limit'])?$_POST['view_limit']:'100','"  style="width:50px; padding-left:5px;"/></TD><TD rowspan="2">
		<TEXTAREA id="str"  name="str" rows="8" cols="60" >';
		$curqry=isset($_POST['str']) ? $_POST['str']:LBL_WRITE_QUERY_HERE;
		$curqry=str_replace(">","&gt;",str_replace("<","&lt;",$curqry));	
		echo $curqry ,  
		'</TEXTAREA></TD><TD>' ;
			
		
		$id_db=$pwd_db->get_db_id($db_name);
		$m_code=NULL;
		
			
		if(isset($_POST["id_menu"]))
		{
			$qry= "SELECT id, name,isif,cof, grp
					FROM menus WHERE id =" .$_POST["id_menu"];
			
			if (($r=$pwd_db->query_arr($qry)) && is_array($r)  )
			{
				
				echo '<span class="selected_menu" >',$r["name"],'</span> 
					<input type="hidden" id="menu" name="menu" value="',$r["name"],'"/>
					<input type="hidden" id="id_menu" name="id_menu" value="',$r["id"], '"/>';
				
				if($r["isif"]=="0")
					$m_code=$r["cof"];
				else
					$m_code= call_user_func($r["cof"], $pwddb);	
		
			}
			else
				unset($_POST["id_menu"]);	
					
		}
		
		
		if( strcmp($_SESSION["QLITE_AUT_USER"],"public"  ) ==0)
			$qry="	SELECT id, name,isif, grp,cof
					FROM
					( 
						SELECT m.id,m.name,m.isif, m.grp,m.cof
						FROM mtodu mu
						INNER JOIN menus m  ON m.id=mu.id_m
						INNER JOIN users u  ON u.id=mu.id_u
						WHERE mu.id_d IS NULL AND upper(u.user) =upper( '". str_replace("'","''",$_SESSION["QLITE_AUT_USER"]) . "')
						UNION 
						SELECT m.id,m.name,m.isif, m.grp,m.cof
						FROM mtodu mu
						INNER JOIN menus m  ON m.id=mu.id_m
						INNER JOIN dbs d  ON d.id=mu.id_d
						INNER JOIN users u  ON u.id=mu.id_u
						WHERE upper(u.user)=upper('". str_replace("'","''",$_SESSION["QLITE_AUT_USER"]) . "')  AND upper(d.db)=upper('". str_replace("'","''",$db_name) . "') 
					) a ORDER BY grp,name;"; 
		else
			$qry="	SELECT id, name,isif,grp,cof
				FROM
				(
				SELECT m.id, m.name,m.isif, m.grp,m.cof
				FROM mtodu mu
				INNER JOIN menus m  ON m.id=mu.id_m
				WHERE mu.id_d IS NULL AND mu.id_u IS NULL 
				UNION 
				SELECT m.id,m.name,m.isif, m.grp,m.cof
				FROM mtodu mu
				INNER JOIN menus m  ON m.id=mu.id_m
				INNER JOIN users u  ON u.id=mu.id_u
				WHERE mu.id_d IS NULL AND upper(u.user) =upper( '". str_replace("'","''",$_SESSION["QLITE_AUT_USER"]) . "')
				UNION 
				SELECT m.id,m.name,m.isif, m.grp,m.cof
				FROM mtodu mu
				INNER JOIN menus m  ON m.id=mu.id_m
				INNER JOIN dbs d  ON d.id=mu.id_d
				WHERE mu.id_u IS NULL AND upper(d.db)=upper('". str_replace("'","''",$db_name) . "') 
				UNION 
				SELECT m.id,m.name,m.isif, m.grp,m.cof
				FROM mtodu mu
				INNER JOIN menus m  ON m.id=mu.id_m
				INNER JOIN dbs d  ON d.id=mu.id_d
				INNER JOIN users u  ON u.id=mu.id_u
				WHERE upper(u.user)=upper('". str_replace("'","''",$_SESSION["QLITE_AUT_USER"]) . "')  AND upper(d.db)=upper('". str_replace("'","''",$db_name) . "') 
				) a ORDER BY grp,name;";
	 
 
	
	function rec_menu($db,&$grppath,$i, &$cur_rec )
	{
	
		
		if($i>=count($grppath))
		{
			$path=$cur_rec["name"];
			 
			if(!isset($_POST["id_menu"]) || strcmp($_POST["id_menu"],$cur_rec["id"])!=0)
				$res='<li onclick="javascript:force_submit(`main_frm`, `id_menu`,' . $cur_rec["id"] . ')">'. $path .  '</li>';
			else 
				$res='<li  class="selected_menu">'. $path .  '</li>';
			return $res;
		}
		$res="";
		 
		$last=$grppath[$i];
	
		if(	count($grppath)>1 && $i< count($grppath) && $i>0)
			$res.='<li class="hm_sub_menu" >'. $grppath[$i] . '<ul>';
		else
			$res.='<li>'. $grppath[$i] . '<ul>';
		 
		do{
			
			$res.=rec_menu($db,$grppath,$i+1,$cur_rec );
			if( !(is_array($cur_rec) && $i<count($grppath) && strtoupper($last)==strtoupper($grppath[$i])))
				break;
			
			$cur_rec=$db->next();
			 
			if(is_array($cur_rec))
			{
				$grppath=explode(".",$cur_rec["grp"]);
			}
			
		}while(is_array($cur_rec) && $i<count($grppath) && strtoupper($last)==strtoupper($grppath[$i]));
			 
		$res.='	</ul></li>';
		 
		return $res;
	}
		
					
		if ( ($r=$pwd_db->query_arr($qry)) && is_array($r) )
		{
			if(!isset($_POST["id_menu"]))
			{
				echo  '<span class="selected_menu">' ,$r["name"] , '</span> 
				<input type="hidden" id="menu" name="menu" value="',$r["name"],'">
				<input type="hidden" id="id_menu" name="id_menu" value="' ,$r["id"], '">';
					
				$m_code=$r["cof"];
				
				$r=$pwd_db->next();
			}
			$i=0;
			
			if ($r && $r["grp"]=='')
			{
					do{	
						
						if(!isset($_POST["id_menu"]) || strcmp($_POST["id_menu"],$r["id"])!=0)
							
						echo '
							<span class="button_to_link"  onclick="javascript:force_submit(`main_frm`, `id_menu`,',$r["id"],')">', $r["name"],  '</span>';
					 
						
						$r=$pwd_db->next();
						++$i;
						if($i%5==0)
							echo '<br>';
					}while($r &&  $r["grp"]=='');
			}
			
			if (is_array($r))
			{
				
				 
				echo '<ul class="hormenu">';
				$grppath=explode (".",$r["grp"]);
				
				do
				{
					echo rec_menu($pwd_db,$grppath,0,$r );
					
				}while(is_array($r));
				echo '</ul>';
				  
			}
			$pwd_db->free();
		}
		
		echo '</td><tr><td id="vermenu_container">'   ;
		
		
		$qry="SELECT    t.name TableName, c.name ColumnName ,c.type
						FROM sqlite_master t,PRAGMA_TABLE_INFO(t.name) c 
						ORDER BY  t.name,c.name";
		
		if(  ($r=$s3->query_arr($qry)) && is_array($r))
		{
			echo '<ul id="vermenu">';
			$last=NULL;
			do{
				if(!$last || $last !=$r["TableName"])
				{
					if($last)
						echo '</ul></li>';

					echo '<li onclick="javascript:cat_sql_id(`str`,`',
					str_replace("<","&lt;",$r["TableName"]) ,'`)">'
					,$r["TableName"] , '<ul>'; 
					str_replace("<","&lt;",$last=$r["TableName"]);
					
				}
					
				echo '<li onclick="javascript:cat_sql_id(`str`,`',str_replace("<","&lt;",$r["ColumnName"]) ,'`)">'
					,str_replace("<","&lt;",$r["ColumnName"]) , "(",$r["type"] , ')</li>';
				
				}while (($r=$s3->next()) && is_array($r));
			
			echo '</li></ul> ';
			
		}
 
		 
		 
		echo '</TD><TD id="menu_container" class="menu_container">',  $m_code?$m_code:"",  '</td></TR> <TR ><TD>';

		
		$qry ="	SELECT name,qry,coalesce(grp,'') grp 
				FROM qLite_qry "  
				. ($user_can_write==false?"WHERE adminqry=0":"") . "
				ORDER BY grp,name"	;
		
				
			if(($r=$s3->query_arr($qry)) && is_array($r))
		{		
			$i=0;
			if ($r["grp"]=='')
			{
				do{
						echo '<span class="button_to_link" onclick="javascript:document.getElementById(`str`).value=`', 
						str_replace('"','&quot;',
								str_replace("\n","\\r\\n",
										str_replace("'","\'",$r["qry"]))), ';`;">',$r["name"],'</span>';
						
						$r=$s3->next();
						++$i;
						if($i%5==0)
							echo '<br>';
				}while($r &&  $r["grp"]=='');
			
			if($r)
			{	
 
				echo '<ul class="hormenu">';
				do
				{
					$last =$r["grp"];
					echo '<li>', $r["grp"],'
							<ul>';
					do	{
							$t='<li   onclick="javascript:document.getElementById(`str`).value=`'.
								 str_replace('"','&quot;',
									str_replace("\n","\\r\\n",
										str_replace("'","\'",$r["qry"]))). '`">'.$r["name"].'</li>
										'; 
							echo $t;
						 
						}while( ($r=$s3->next())&&  $r["grp"]==$last);
					
					echo '</ul>';
				}while($r);
				 
			}
			
		}
		}

		echo '
		</div>
		</TD><TD><input type="submit" name ="qry_exec" value="Query"/>';
		if($user_can_write)
					echo '<span>		</span><input type="submit" id="exec_sub" name ="qry_exec" value="Exec" >';
		echo '</td></TR>
			<tr><td colspan="3"><div id="serv_container" style="width:99%; padding:0;" ><TEXTAREA id="service" name="service" rows="6" cols="80">', 
		($service ?  $service : '' ) , '</textarea></div></td></tr> 
			</tbody>
		</TABLE>
		
		</form>
		<script>
			document.getElementById(`str`).onkeydown=function (e){
					if (e.keyCode === 9) {
								e.preventDefault();
								var start = this.selectionStart;
								var end = this.selectionEnd;
								this.value = this.value.substring(0, start) +
								"\t" + this.value.substring(end);
								this.selectionStart =
								this.selectionEnd = start + 1;
						}}
					  document.getElementById(`service`).onkeydown=function (e){
							 if (e.keyCode === 9) {
								e.preventDefault();
								var start = this.selectionStart;
								var end = this.selectionEnd;
								this.value = this.value.substring(0, start) +
								"\t" + this.value.substring(end);
								this.selectionStart =
								this.selectionEnd = start + 1;
					  }}
		</script>
		';
		
		}
		
		if($qry_res)
			echo $qry_res;
	echo '<div id="furthertable"></div></body>
	</html>';
	
	if($s3 && $s3->is_valid_db() )
		$s3->close();
	 
	?>


