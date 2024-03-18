# qLite
Database Management System SQLite based

qLite is a Database Management System Server that uses SQLite as storage engine. 
The scope of work:
<ul>
  <li>Works on each platform. You just need a browser.</li>
  <li>Works as server</li>
  <li>Supports Users</li>
  <li>Supports Grants</li>
  <li>Assign databases to Users (where to write CREATE TABLE for example)</li>
  <li>No Hardware required, just few kB of space in a internet site purchased by a provider </li>
  <li>Allow safe write operations also to not experienced users </li>
  <li>Allow people to work in the same database no matter where they are and create small work tasks</li>
  <li>Build masks, MS Access style</li>
  <li>Lightweight, fast and no external dependencies but SQLite. qLite is. Just. A. Single. PHP. File.</li>
</ul>
If it seems too much... no it is not too much, I am surely forgetting something.
Target are internet community that works on the same project. Companies that work with small-medium project. And everywhere several users requires sharing a common database, but they do not want to invest in a server DELL IperPower  99-a_lot_of_0's-20  where to run Oracle Enterprise Edition.

## History

I briefly tell the history of this project. 
I was boting the italian Wikipedia dump, when my bot begins to send me weird answer. In this case Bio's where the Sex was not set. "I hope it found a Teletubby", it wasn't.  After few investigations I found out that there was some parenthesis to fix. This time I was not guilty(ah... never scan the comments of the dump...). I scanned again the dump and I detect all the parenthesis, and I want to give them a relative big file to help them to maintain the wiki . And here comes the problem: how do I give them a structured filed? My provider barely allow me to access to my database, MS Access... No! MS Access No!, but also if I would use it there is no way to allow people scattered across Italy to work on the same db. SQLite requires experienced users, you need a shell or a GUI( matter of taste), and also here no way to share databases.<br> 
Hold my beer...qLite is born.<br>
The name is not obtained from cropping the S, in SQLite, in this case would be capital. The q is dedicated to the C function qsort(). My beloved language and the language which 
SQLite is written.qsort() is one of my preferred functions with the mem...() functions, each time I use this function I am dumbly happy, never understood why. The q in qsort() means "quick", so "qLite"  would mean "quick (and) Lite", that match perfectly with the philosophy of this project. Ok, it is pervert enough: taken.

## Structure

The journey begins with the download of the file `qLite.php`. It is the only thing required. Put it inside your XAMPP folder or in your website. And just type his name. Somoething like : `localhost/qLite.php`  or `yourwebsite.com/qLite.php`. If you use XAMPP, be careful that SQLite is not available by default. So in Windows you have to go to the php.ini file and decomment `extension=sqlite3` and `pdo_sqlite`. Restart Apache. And now should be available. Also on Linux you have to include these extensions, you have to install the php extension, `chown` the folder and give write rights to `others` with `chmod o+w <folder>`. This last step is hard to find in an online documentation and took me several hours to understand what was wrong.<br>
Now a look inside, there are various constants, and probably you need to translate the labels but this one is the one that surely the admin have to change. It is his the admin password, already cripyted, accordingly to the Hash algorithm defined with QLITE_HASH_ALG. Take a look to the pHp hash() function to see which algorithms are available. The default password is  `Bella Ciao`, valid also as default password for the users.<br> 

```
	define ("QLITE_PWD_PWD","e52cbe1aa2f5cf7b68225ad60eb9ba0d5bc376c5481764d70929eb3d65d00512");
	define ("QLITE_HASH_ALG","sha256");
```
 
Inside there is a tool that allow you to compute a new password with a specific password field. So may be you make a first access with `Bella Ciao`  compute the new one and overwrite the existing one.<br>
You can change the algorithm, maybe you have already a set of users and you want use their password. You have to do it programmatically and remember to update when they change password.<br> 
At the first access it creates a new database, if you do not change anything, will have the path  `qLite.sqlite3` and the name `main` . This database is the brain of qLite, here are saved all the users password, the paths of the databases managed by qLite and all the menus (we will see later).<br>
The table `users`, has three fields, `user`, that is the nickname, unique case-insensitive, so JURHAS or jurhas  are exactly the same. Behaviour inherited from SQL. Since there is a UNIQUE INDEX upper(user), remember that to allow the database to use this indexes in the query plan, the WHERE clause must match this expression. But I do not think you are never going to have so many users or db's to experience performances problems. But I have to advise you.  Ever in the table `users` there is the field `pwd`, where is stored the crypted password. It is useless for `admin`, his password is saved in the header as already seen,  `public` opposite problem, he does not need also to authenticate him self, and the groups. Using the postgreSQL definition of group "the group is a user that cannot login" . So to simulate this behavior, just set `pwd` to a not valid sha256 value. I would suggest simply 'group', so you can filter easily. There is a further field, the day I felt generous, it is `lev`,  it gives you another way to select a group of users, may be 'superuser' and 'user', the logic is completely up to you. I did not use it in any place, so you can assume every logic. To add a new user, use the query displayed bottom left insert->User.  The password displayed is also `Bella Ciao`. There is a table named `utog` where to define the group membership. I did not use it in any place, you can modify it, drop, whatever you want. As sysadmin, I suggest that you do not serve the group, but the group must serve you, if work with group does things complicated, do not use it. Very simple.<br> 
The second table is `dbs`, where are stored all the databases paths and the name to display. A path can be C:/users/.../wp.sqlite3, meanwhile the name to display can be just wp. Inside the database, we refer to the databases only with the name, stored in the field `db`. Therefore, also `db` is unique, case-insensitive. `Path` has no unique index,  so there could be two different rows that point to the same database. If it scares you, create a unique index:  `CREATE UNIQUE INDEX u_ix_dbs_path ON dbs(upper(path));` It is not completely bulletproof, since an absolute path and a relative path can refer to the same db and no way to detect it. The last field is `grp`, it is the abbreviation of group, but I could not use it as name since it is a SQL keyword.  I think that with this basic settings you can manage up to a hundred databases without big problems. So to visualize them inside a more comfortable drop-down menu, we set `grp`, meanwhile if grp is not set, it will be shown outside, to have a quicker access to them.<br>
To insert/create a database with  qLite, you have to use the specific Admin->New Database that you can find in the Top-Right.  This is the only place where SQLite is open with the flag CREATE. Here you have to insert  the path. This  will create a new one or add an existing one. Inside the new added database qLite will create a table, qLite_qry, where the users can store their queries. The queries will be displayed on the left bottom of the control panel, also here if `grp` is set, the query will be added inside a dropdown, meanwhile if not set outside. There is a `UNIQUE (upper(name),upper(query))`, that means that inside a group cannot exist two queries with the same name. It is not possible to exclude the creation of this table and to read it, if you want to modify it you have to do it programmatically.<br>
After you insert it, we change the name of the database, and you have to do with the query update->Database Name. Refer to it with the path, put a  db name. After this, you can forget the path. Now you have to refer to this database only with the name. The reason for this two-step solution, is the error handling, it is hard to understand what to do in case of error. <br>
We create a user, we create a database. Now we Grant a user to a database.  A user can only access to the database where is granted. Also, if a database is granted for public, the user must be granted. To do it, left-bottom  Grant->User To Db.  The SQLite database can be opened read-only or write, these are the only two options available, so  the field `canwrite` refer to the possibility of the user to write: create tables, insert,delete, update.... A user should never be allowed to write in shared databases (also if he has to write). The `canwrite` should  be set to 1  if a user has exclusive access to a database, let say his sandbox or  is a trusted superuser.<br> 

Now you can create how many databases you want, how many users you want and cant grant them. But there is a problem. If the database serves a team, you cannot give to 10 persons the rights to write DELETE FROM table. If there is also just 2 persons that can do it, there are still 1.5 person too much. So the most democratic thing: nobody can write.<br>
We need a trap-door, where users cannot put their hands and work with defined queries. The solution is the menu. The name comes from the classical menu bar of windows.Maybe you would call hit "plug in", you have to consider the history of a solution, begins to solve a small problem, you understand that can solve a lot of other problems, the first problem becomes a side quest, but the name remains, I mean, also in "Wall Street" there is no wall. Surely I am not the most romantic man in the world I never buy flours to my lady and if I buy her chocolades I am going to eat all of them, but I like to remember the labor of an idea. From the menu, every user can write in each database, also if he has no access rights, he can write also in the main. Cause this power all the menus are stored inside the main, and only the admin can build them, or, if he needs help, he has to overview them, and the first thing to check is in which database writes.  Menus becomes a way to put code inside our GUI and can serve different purposes. We will see few examples later. I am not going to convince you that this is the best solution, of course a specific application is better. But, first, now you can manage several databases, users, grants, acceess as server also if you have a small internet site, therefore is a good base to start, second: the costs for a specific application grow quickly and small team of 4-5 peoples cannot surely invest 80% of their time to develop a specific application. This would be justified only for big  and/or long term projects. But also if it is not the best solution, it has still some advantages: you can filter and sort in each way you want, and with a specific application this is not possible, also if you invest a lot of time. What do we do is minimize the discrepancy,  work safe,  get quasi-dedicated form, but investing the less as possible. <br>
Now we have another problem. The menu works fine, but would be nice to select from a table the values and avoid typos and save a bit of time. Let's hack. The answer table has assigned a class which name is composed of the concatenation of the header. This means that if we write SELECT one,two, foo ...  the table has `class="onetwofoo"`. 
Now we can detect if the user works with the correct query, and if he does, it is very simple to fill the fields and update,delete or inherit.   
How do we crack this? Just use alias SELECT '10' one,'30' two, 'ed'  foo, you can force whatever you want. But surely a spreadsheet does not solve the problem. So this just a weird way to delete the item 30 clicking on 20. There is also the possibility to put a log, to discourage Neo to make the hack of the century.  Be careful that if you use simple tables, id,name ... may match also other  tables that serve differently purposes. In this case, enforce the header with alias and use more columns. If an alias contains, spaces,  diacritical charachter, dots... invalidate the class name and no class will be assigned. You can cleary understand that is very hard to escape 65000 charachters of the unicode set of charachters, so I did not even tryed. The work query header should be composed only for the letteres and number in other words this function must return `true`:<br>

```
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
```

## Menu SQL

The table is the follow:
```
		CREATE TABLE IF NOT EXISTS  menus( 
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		name TEXT NOT NULL CHECK(length(name)>0),
		isif INTEGER DEFAULT 0 NOT NULL,
		cof TEXT CHECK( iif(isif>0, cof IS NOT NULL AND length(cof)>0,1)), 
		nameof TEXT DEFAULT '',
		grp TEXT NOT NULL DEFAULT '',
		description TEXT,
		UID INTEGER,
		dateid TEXT,
		db TEXT NOT NULL DEFAULT '');
```
 `id`, the uniqueness is granted for (name,grp), so we have to identify a menu with his id, otherwise becomes uncomfortable.<br>
`Name`: is the name to be shown.<br>
`isif`:  if 0,  `cof` (the next field) is raw html/javascript code, if 1 `cof` is the name of a function that get called before the submit.<br>
`cof`: can be code or the name of a function accordingly to isif. You have to define the function.<br>
`nameof`: NULL or the name of a function that get called after the submit. You have to define the function. <br>
`grp`: `grp` here, and only here, accepts also paths, separated by a dot  for example: Tools.String . This will build a submenu. The number of menus can grow very quickly so we need better instruments.<br>
`description`: this is for your use, when you are going to have several menus you will need a brief explanation, rather to read the whole code.<br>
`UID`: like  GUID but not a GUID, I like a readable number, this is the way I identify my menus, so can I give you some support. It starts from 1,000,000,001, under this number you can use also for your scope.<br>
`dateid`: the last change for the menu<br>
`db`: It can assume each value, not neccessary a db name. When you are going to write menus for a specific database you can cleary understand that under the same `grp`, let say "insert", different databases can have the same menu. Insert->City for example, but are different menus, that work in different databases with different structures. To guarantee the uniqueness of a menu inside a database, we have to set a unique index, but only `name` and `grp`  would be not  enough  or you use unique names C style DBNAME_XXX or we set this further variable, C++ namespace style. But has no other purposes and no cross checks, it will not get displayed, it will no prevent inserts in other databases, it will not grant himself in the datbase, it has no other functions, completely up to you to check his correctness. The uniqueness is (name,grp, db).<br>

Now we grant them: 
```
	CREATE TABLE IF  NOT EXISTS mtodu( 
			id_m INTEGER NOT NULL,
			id_d INTEGER, 
			id_u INTEGER
   	);
```
Despite his simplicity, it took me a couple of days to define his structure. First I did it in two tables, then triggers,after several attempts I understood that there is no way to do all the job with a single query, but we have to split the problem.<br>
Of course id_m (menu) must be NOT NULL, he is our main character. But id_d(dbs) and id_u(users)... why not?
So there are 4 cases:
<ul>
	<li>id_d IS NULL, id_u IS NULL  The menu is available for all users and inside each database. Typically, a safe tool</li>
	<li>id_d IS NULL, id_u=5  The menu is available in each database, only for the id_u=5(an example). Typically, super-user powers</li>
	<li>id_d=3, id_u IS NULL  The menu is available only inside a specific database for all granted user. Typically, the write functions of this specific db</li>
	<li>id_d=3, id_u=5  The menu is available only inside a specific database for the specific user. Typically, team-manager powers.</li>
</ul>
If you promote a menu from shared to reserved, remember to delete first the existing grant, because until exists this row the menu is shared.<br> 
The user public must be granted for each menu, also the safe tools.<br>
Practically you can assign menus with surgical precision, this is the true grant.

## Menu php/html

When we write a menu that is going to submit, we must know which fields will be processed. The inputs that submits must have the name `b1` or `b2` or `b3`. You can easily increase this number adding rows to the variable `$submits`, in the same way you can increase the number of the other macros .  With each one of these buttons is associated a query, respectively `qry1,qry2,qry3`.<br>
These queries will contain macros. For example: {s1}.<br>
What value we have to give to this macro?<br>
These macros are defined with the variable `$tags`, you can increase the number of macros and you can change the aspect of them. A row of this variable is so composed:
```
 array("s1","{s1}","s")
``` 
the first element, `s1` is the name of the &lt;input&gt; that contains the value that is going to replace the macro `{s1}`. The third element `s` tells that is  a string  and it requires to be quoted and escaped, meanwhile `n` tells that is a number.  qLite  defines macros from s1 to s6 and from n1 to n6. You can easily increment this number.<br>
So the  &lt;input&gt; will be:
```
 	<input  name="s1" value="Hello World">
```

The query, `qry1` in this case, will be hidden, nobody can put hands over it:
  
```
	<input  type="hidden" name="qry1" value="INSERT INTO foo VALUES({s1})">`
```

This  query will be executed if the user clicks on the submit `b1`:  

```
	<input  type="submit" name="b1" value="Insert an Hello World">
```

You can specify the follow variables, ever with &lt;input&gt; and  also these hidden:
`wdb`, will execute the query in a different database and not the current.<br>
 `lqry` is a "log" query, ever executed if defined.<br>
`ldb` is the database where to execute the "log" query, if different, otherwise it is not required. <br>

a basic implementation will be:

```
	<input type="submit" name="b1" value="Close">
	<input type="submit" name="b2" value="Open">
	<input type="hidden" name="qry1" value="UPDATE pag SET chiusa=1 WHERE id={n1}">
	<input type="hidden" name="qry2" value="UPDATE pag SET chiusa=0 WHERE id={n1}">
	<input type="hidden" id="n1"  name="n1" value="">
```

The first two rows define two buttons, with the first one is binded the hidden `qry1` and with the second `qry2`.<br>
Both these query, contain the macro `{n1}`. This value will be replaced with the value of the last hidden input named `n1`. This will updated programmatically, when the user clicks on the table. <br>
In this way you do not have to write php functions, and you can handle everything whit the database, you can quickly perform write operations without occur in accidental UPDATE without WHERE. 
 
## Menu Javascript

The menu is written in Javascript, and we need some handlers.<br>
At the load of the page it calls `on_doc_load()`, that you have to define if you need<br>
The answer table has class composed from the concatenation of the headers (if valid of course, otherwise nothing, no way that can be a valid query)<br> 
Each cell has class named after his own header. This can be a bit dangerous, can match existings classes, I used complex name, but I cannot give warranty, if some weird behaviour happen use an alias. <br>
With each cell is bounded a function called `on_tbl_clk(row,col)`, that you have to define.<br>
There is a `<div with id="serv_container">` where there is an empty, and hidden `<textarea id="service">`, that show up when get filled. If you need more controls, you need to add them programmatically. <br>
There is at the very-end a `<div id="furthertable">` where to show, other tables loaded with AJAX.<br>
If you use AJAX, you have to do it with `POST` and you must set a variable named `ajax`. The  query have to be specified in the  variable `aqry` and if you need a different database you have to specificate it with  `adb` so a tipically AJAX request will be:

```
	var par=`ajax=1&aqry=` + `SELECT par,row,col FROM par WHERE id_p=` + tbl.rows[r].cells[0].innerHTML ;
	xhr.open(`POST`, `qLite.php`,true);
```
`Par` are the variables to pass: the  first variable  is `ajax`, we give a value not important what, the second is `aqry`, with our query.<br>
Second row: we open a request `POST`,inside `qLite.php`, this is the reason you have to set `ajax`, it have to exclude all the rest of the code, if you do not set it,you get a clone of the GUI.<br>
Up to you where to display it. <br>

A simple example:<br>
My site: I want to allow people to click on a link and open the relative wikipedia page. I must escape the query or people can write malicious code. So I cannot use links inside the answer table. I catch the column where is stored the link, that is named `path`, therefore his class is also `path`. In the `on_doc_load()` function I define the style to format the column as link,and comunicate people that this column is clickable. And when he clicks, it calls the function `on_tbl_clk`, I check if he clicked the right column and then I open the link consequently. 

```
	<script>
	function on_doc_load() 
	  { 
	  
		var styleSheet = document.createElement("style");
		styleSheet.innerHTML=".path td {color:#069;text-decoration: underline;cursor: pointer;}";
		document.body.appendChild(styleSheet);
		} 
	  
	function on_tbl_clk(r,c) 
 	{ 
		var tbl= document.getElementById("anstbl"); 
		if(tbl.rows[0].cells[c].innerHTML=="path") 
			window.open("https://it.wikipedia.org/wiki/" + tbl.rows[r].cells[c].innerHTML , "_blank");
	} 
	</script>
```

A complete example:<br>
We define a button where we suggest the rigth query, the one that allows the users to work. The query is `SELECT id,titolo,autore,chiusa, '' p ...` so the class name will be `idtitoloautorechiusap`.<br>
A bit HTML to define the inputs and a table where to show the resume of the current operation.<br>
 `on_doc_load()` we  define the styles to allert people that Ok! this is the right query. <br>
In the `on_tbl_clk()` function we check first if the query is the right one: until this menu is selected does not matter if it is the right query or less, this function will process each click of the user, therefore we must exit if wrong.  We toggle the  selected row, we set the input `n1` with the id, this is important, otherwise the queryes are not executable, and we open an ajax request, the same as above. I show the answer in the same table, It is nice to see and comfortable. And last, if he clicks in a specific cell, will open the relative wiki page.  

```
	<span style="position:relative; top:0%; left:0%; "> Get query<br></span>
	<button  type="button"  onclick="javascript:set_val(`str`,`SELECT id,titolo,autore,chiusa, '''' p FROM pag WHERE chiusa=0;`);">Get Query</button> 
	<button   type="button" onclick="javascript:document.getElementById(`help`).style.setProperty(`display`,`block`);"> Help</button>
	
	<span style ="text-align: left; display:none;" id="help">Clicca su Get Query, imposta i filtri. Ricorda che su SQLite LIKE è insensibile al maiuscolo quindi WHERE titolo LIKE ''%mondo%Infinito%'' restituira anche ''IL MONDO é INFINITO'' mentre = e sensibile al maiuscolo quindi WHERE autore=''Pippo'' restituirà esclusivamente Pippo. Se volete renderlo non sensibile al maiuscolo, convertite ambo i membri a maiuscolo o minuscolo ad esempio  WHERE upper(autore)=upper(''Pippo'')<br>
	</span>
	<table id="menutbl" class ="tblfrm tlbmenu" >
	<tr><td>Id </td><td id="wp_id"></td></tr>
	<tr><td>Titolo</td><td id="wp_titolo"></td></tr>
	<tr><td>Autore</td><td id="wp_autore"></td></tr>
	<tr><td><input type="submit" name="b1" value="Chiudi"></td><td><input type="submit" name="b2" value="Riapri"></td></tr>
	</table>
	<input type="hidden" name="qry1" value="UPDATE pag SET chiusa=1 WHERE id={n1}">
	<input type="hidden" name="qry2" value="UPDATE pag SET chiusa=0 WHERE id={n1}">
	<input type="hidden" id="n1"  name="n1" value="">
	
	<script> 
		var _row=-1;
	function on_doc_load() 
	{ 
		if(document.getElementsByClassName("idtitoloautorechiusap").length<1) return; 
		var styleSheet = document.createElement("style");
		styleSheet.innerHTML=".idtitoloautorechiusap  {float:left;}  .idtitoloautorechiusap tr {color:#069;text-decoration: underline;cursor: pointer; } .selRow,.anstbl .selRow:nth-child(odd)  {background-color:#99EDE2;}";
		document.body.appendChild(styleSheet);
	} 
	  
	 function on_tbl_clk(r,c) 
	 { 
	  
			var col=document.getElementsByClassName("idtitoloautorechiusap");
			if(col.length<1 || r<1 ) return; 
			var tbl=col[0];
			if(r !=_row && _row>0)
			{
				tbl.rows[r].classList.toggle ("selRow");
				tbl.rows[_row].classList.toggle ("selRow");
				tbl.rows[_row].cells[4].innerHTML="";
			}else if(r!=_row)
					tbl.rows[r].classList.toggle ("selRow");
			
			document.getElementById("n1").value=tbl.rows[r].cells[0].innerHTML;
			document.getElementById("wp_id").innerHTML=tbl.rows[r].cells[0].innerHTML;
			document.getElementById("wp_titolo").innerHTML=tbl.rows[r].cells[1].innerHTML;
			document.getElementById("wp_autore").innerHTML=tbl.rows[r].cells[2].innerHTML;
			var xhr = new XMLHttpRequest();
			var par=`ajax=1&aqry=` + `SELECT par,row,col FROM par WHERE id_p=` + tbl.rows[r].cells[0].innerHTML ;
			
		
			xhr.open(`POST`, `qLite.php`,true);
			xhr.setRequestHeader(`Content-Type`, `application/x-www-form-urlencoded; charset=UTF-8`);
		  xhr.onreadystatechange = function () {	
			var DONE = 4; // readyState 4 significa che la richiesta è stata eseguita.
			var OK = 200; // lo stato 200 è un ritorno riuscito. 
			if (xhr.readyState === DONE) {
			  if (xhr.status === OK) {
				//document.getElementById(`furthertable`).innerHTML = xhr.responseText;
				tbl.rows[r].cells[4].innerHTML=xhr.responseText;
			  } else {
				console.log(`Error: ` + xhr.status); // Si è verificato un errore durante la richiesta.
			  }
			}
		  };
		 xhr.send(par);
		_row=r;
		if(c==1)
			window.open("https://it.wikipedia.org/wiki/" + tbl.rows[r].cells[1].innerHTML , "_blank");  
			
	}
	</script>
```



# Step by Step Configuration

Ok probably until now is it not all clear. Now we are going to show a whole step by step configuration.

### The first login 

![FirstLogin](https://github.com/jurhas/qLite/assets/11569832/b2a8d099-415f-4c41-8abd-82a3727c8606)

If admin digits the wrong database, (empty is surely wrong),  he will readressed to the `main`.<br>
The first password is `Bella Ciao`.<br>
The first login creates the main database.<br>

### Welcome on board

![Sha](https://github.com/jurhas/qLite/assets/11569832/fd4a3087-6703-43d7-b9d7-b23b649b7aaf)

Welcome inside qLite.<br>
From Top, clockwise:
<ul>
	<li>In red is shown the current menu. It is persistent also after a submit.</li>
	<li>If availables (not here), at the right of the current menu, are shown the quick-access menu</li>
	<li>Imediatly under it there are the menu lists.</li>
	<li>The Menu control panel, allows to create small masks. In this case we have the tool to compute the SHA256 of our new  password. It is so named, but if you change the alogrithm of hash defined with  `QLITE_HASH_ALG` it will computed with the new algorithm, but this label will be not updated, you have to change it in the menu definition. There is no control, so admin must impose himself a strong password.</li>
	<li>At the bottom, hidden if empty, there is a &lt;textarea id="service" &gt;. This is a comode space where to display outputs, work outside the main textarea and prepare strings</li>
	<li>The &lt;textarea id="service" &gt;, is inside a &lt;div id= "serv_container"&gt; ,if the Menu Control Panel is not enough, here you can add other controls, ever inside the &lt;form&gt;, so if required they will also submit. </li>
	<li>Now there are a sequence of dropdowns, that are the saved queries in the table `qLite_qry`. If the query is inside a dropdown, means that his `grp` is set, and his name is  "delete" or "insert"...  Clicking on it, be carefull, it will set the main textare with the query. So if there is another query, this will get lost.</li>
 	<li>Above this dropdowns there are the quick access queryes. That simple has `grp` not set.</li>
	<li>Here is not highlighted, there is the table list, if you click on the table name, it will concatenate the table name in the main area, if you click on the field name, it will concatenate the field</li>
</ul>

Now we copy our new password, and we open the file `qLite.php` with a text editor. 

![Change PWD](https://github.com/jurhas/qLite/assets/11569832/39baec44-1703-45aa-88b1-831b27b20aa3)

We search for the constant `QLITE_PWD_PWD`, and overwrite it with the new computed password. Done.

### New Database

Now we create a database.
We select the menu Admin->New Database :

![New DB](https://github.com/jurhas/qLite/assets/11569832/580b55f6-1d6e-494a-b55a-d0bff07538dd)

<br>
We write our database path. And click on Ok<br>
This operation will fail if:
<ul>
<li>The path is an existing file, but not a valid SQLite3 database. Let say a .txt file</li>
<li>The path points to a not existing folder. Let say foo/db.sqlite3, if foo does not exists it fails</li>
<li>In Linux, if he has now write permissions</li>
</ul>
If suceed the  new added database, will be shown on the right of our current database, but his name is still the path.<br>
We select the query, update->Database Name , 

![rename DB](https://github.com/jurhas/qLite/assets/11569832/9f64f16d-9834-4094-8afe-61197d0237f8) 

<br>
We set the name in the field `db` and if required `grp`. The row is still identificable with the path. <br>
After you run the query, the new name will be imediatly available, and we will speak never again about the path. Now he is `db`.<br>

### New User and Grant

New user and grant can be done just with SQL queryes. So insert->New User:

![new user](https://github.com/jurhas/qLite/assets/11569832/de7bbe65-0f4c-4812-bee7-b2541820f157)

<br>
we set the name... done!<br>

Grant->User To DB :

![Grant](https://github.com/jurhas/qLite/assets/11569832/7d33de25-7a73-4fb6-964e-1b4e33faf62d)

Now the user "Jurhas" can access to the Database "wp", but cannot write.

### Menus!!!

For this project I prepared a Menu, and stored in a file. Therefore we have to execute this file. Tools->SQL File 

![File](https://github.com/jurhas/qLite/assets/11569832/f736772a-6693-4e47-a61a-fa608dad90d0)

<br>You can choose if you upload a file, or run a local file. In this case I choose a Local File. If you upload it, it will not be saved.<br>

Now we have to grant the menus, we prepare our grant list:<br>

The db is `WP`<br>
all users require `wiki_p`<br>
admin requires `WP`, and `Save Query` (admin is already granted for Save Query but I want to show you the concatenate tool, if you run these query you are going to have a constraint violation, just do not insert it) <br> 
Jurhas, our team-manager, requires `Save Query` <br>

`Save Query` is a tool to save the queries in the table qLite_qry. Nobody can write so, teoretically, nobody could save query inside. You cannot give this menu to all, or surely the people begin to save "SELECT 2+2" "very important query". All users are going to load these query, therefore all users are going to read these "very important query". This means, that only the team-manager can save the query.<br>

Query: grant->Menu <br>
This query is splitted in 4 parts and commented.<br>
We select the query "Menu Valid only inside a database, for all granted  users"<br>
We look for  the id for the menu wiki_p, in my db is 11 so we fill up  the fields and we get :

```
INSERT INTO mtodu(id_m, id_u, id_d)
SELECT m.id, NULL,d.id
FROM menus m,dbs d
WHERE m.id IN (11)  -- menu id here
AND upper(d.db) IN (upper('wp')); -- db name here
```
This grants `wiki_p` to all users.<br>

Admin requires 2 menus, ( but read 10 menus, you cannot surely remember all the id's) 

We activate the menu Tools->String->Concatenate<br>
The menu `Concatenate`, allows to select values from a table and concatenate in a string, if text is checked, the value will be escaped and wrapped inside the '' and if upper is checked they are also wrapped inside the upper function.<br>
We select the menu list:  select->Menus. In the picture I set a filter so can fit in an image, but you are going to have much more menus.<br>

![Concatenate](https://github.com/jurhas/qLite/assets/11569832/1f10a661-9466-483d-81df-2e2c0d61d62f)

We have to grant the menus 6 and 9<br>.
Again grant->Menus <br>
This time we select the query "Menu Valid only inside a database for only specified users", we fill up the fields and we have<br>

```
INSERT INTO mtodu(id_m,id_u,id_d)
SELECT m.id, u.id, d.id
FROM menus m,users u,dbs d
WHERE m.id IN (6,9) 			-- menu id here
AND upper(u.user) IN (upper('admin')) 	--user name here
AND upper(d.db) IN (upper('wp')); 	--  db name here
```
We  do the same with Jurhas, with the same query but only for the menu 9

```
INSERT INTO mtodu(id_m,id_u,id_d)
SELECT m.id, u.id, d.id
FROM menus m,users u,dbs d
WHERE m.id IN (9) 			-- menu id here
AND upper(u.user) IN (upper('jurhas')) 	--user name here
AND upper(d.db) IN (upper('wp')); 	--  db name here
```
Done. A bit complicated, but the grants are ever so hard. You can understand the importance to define user groups, to define handlers to select group of menus (the field `db`), and the importance of the shortcuts id_d NULL and id_u NULL e why I was so stubborn to include these functionality.<br> 


### Wp
Activate Tools->Wp

![Screenshot 2024-03-14 144842](https://github.com/jurhas/qLite/assets/11569832/64bc7990-cd98-4ace-9a2b-e43ae2c596bd)

We put the file name par.txt and it will load our table in just 2 seconds.<br>
The Tool Wp, that is included also in the basic function, is a tool that execute the member function load_file($fname). This is. So if you need to load files programmatically, you can change this function accordingly to your file. The problem with pHp is that has no efficient string builder. So if you have  to process a binary file, can be painfull. When I work in C  I do not use prepared statments, because I work with differents databases and  I prefer build valid SQL statments and have portable code. My string library is efficient, you cannot appreciate the difference. The first versione of this function  was with this approach, and it cost me 20 minuts, yes, with my old laptop where runs Linux, but 20 minutes. I have already experimented that the combination of `fgets()` and `explode()` is efficient also in pHp, so if it is possible build your file  writing a record in a row, separated by a defined sequence of charachters,  `fgets()`+`explode()`+`prepared statment` does the job well. But if your record is splitted in more rows or you have to read a binary file and concatenate charachters until you find the NULL... opss. If somebody reads... we need an efficient string builder, it is just a single function `str_concat(&$str,&$mixedvalue)`, that rather than create a copy, that will trigger an O(n^2), increases `$str` passed by reference and returns him self, also the other arguments must be passed by reference, otherwise also them are going to create a copy, this is much less important it is just a O(2*n), but I mean, if the purpose is to build an efficent string builder, we do it efficent. Of course... if the strings are buffered... otherwise: we need a class.<br>  

## Compliments, it's a beautiful GUI

Ecco a voi: 

![Here](https://github.com/jurhas/qLite/assets/11569832/92a4c6a1-efe2-4223-b5d2-9053815b11ac)

There is a joke of this girl that ask her mother why she cut the tips of the sausages when she put them in the pan... and she says that has seen it from her mother but does not know why, so ask gran-mother same answer, ask grand-grand-mother and she says:  "what? You still didn't buy a bigger pan?". A lot of GUI that run on a browser try to copy the behaviour of Winodws, the ListView... What? You still do not use the HTML table? It is incredible how nice  and comfortable reports you can do with this instrument.. 

## TODO List

This is cleary the first version, the beta version. I plan to do:
<ul>
	<li>Syntax Highlighter</li>
	<li>Full Text Index... poor-man full text index. But there is no way to make a rich-man full text index, also if I was able to put hands inside the SQLite code, and insert an index in a database is not surely the easiest thing in the world, the library is from pHp not mine, so no way. But with LIKE we can scan I think until few thousand  rows without big problems.</li>
	<li>Give you support with my web site, and therefore I will give you a tool to check the new menus or the updates for the released one </li>
	<li>Fine work with the styles</li>
 	<li>Fine work with all behaviours</li>
	<li>At this purpose each feedback is really welcome, write me here or  to andrea_spanu(at)yahoo.it</li>
	<li>If the next version is not compatible with the current, a will give you a migration tool</li>
</ul>
 If you do changes inside take note, if you want change styles, include scripts, make it with an extern file.<br>
 I go back to my wiki dump, so I am going to find the real needs and the problems. I plan the next release in May.

