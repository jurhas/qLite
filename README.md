# qLite
Database Management System SQLite based

qLite is a Database Management System Server that uses SQLite as store engine. 
The scope of work:
<ul>
  <li>Work on each platform. You just need a browser.</li>
  <li>Work as server</li>
  <li>Support Users</li>
  <li>Support Grants</li>
  <li>Assign databases to Users (where to write CREATE TABLE for example)</li>
  <li>No Hardware required, just few kb of space in a internet site</li>
  <li>Allow safe write operations also to not experencied user </li>
  <li>Allow people to work in the same database no matter where they are</li>
  <li>Allow to implement small masks, MS Access style</li>
  <li>Create small work tasks</li>
  <li>Lightweight, fast and no external dependencies but SQLite. qLite is just a single. pHp. file.</li>
</ul>
If it seems too much... no it is not too much I am surely forgeting something.
Target are internet community that works on the same project. Companies that work with small-medium project. And everywhere several users requires to share a common database but they do not want invest in a server DELL Power PQRST 55-(a lot of 0's)-20  where to run Oracle Enterprise Edition.

# History

I briefely tell the history of this project. 
I was boting the italian Wikipedia dump, when my bot crashes. After few investigation I found out that there was some parenthesis to fix. This time I was not guilty. I scanned again the dump and I detect all the parenthesis, and I want to give them a relative big file to help them to mantein the wiki . And here comes the problem: how do I give them a structured filed? my provider barely allow me to access to my database, Access... No! Access No!, but also if I would use it there is no way to allow people sparsed in Italy to work on the same db. SQLite requires experencied users, you need a shell or a GUI( matter of taste), and also here no whay to share server access... 
So... hold my beer...qLite is born.
The name is not obtained from cropping the S, in SQLite, in this case would be capital. The q is dedicate to the C function qsort(). My beloved language and the language which 
SQLite is written.qsort() is one of my prefered functions among with memset(), each time I use qsort() I am dumbily happy, never understood why. The q in qsort() means "quick", so "qLite"  would mean quick [&] Lite. That match pefectly with this project. Ok it is pervert enough: takken.

# Structure
The journey begins with the download of the file qLite.php. It is the only thing required. Put inside you xampp folder or in your website. And open it typing simple his name. So localhost/qLite.php  or yourwebsite.com/qLite.php. If you use XAMPP, be careful that SQLite is not available by default with php. So in Windows you have to go to the php.ini file and decoment extension=sqlite3 and pdo_sqlite. Restart apache. And now should be available. Also in Linux you have to include these extension, you have to install the php extension, chown the folder and give write rights to others with chmod o+w &lt;folder&gt; .This last step is hard to find in a online documentation and took me several hours to understand what was wrong.<br>
Now a look inside, there are several constants, and probably you need to translate the labels but this one is the one that surely the admin have to change. It is his the admin password, already cripyted, accordingly to the Hash algorithm defined with QLITE_HASH_ALG. Take a look to the pHp hash() function to see which algorithms are available. The default password is  Bella Ciao , valid also as default password for the users. 

	define ("QLITE_PWD_PWD","e52cbe1aa2f5cf7b68225ad60eb9ba0d5bc376c5481764d70929eb3d65d00512");
	define ("QLITE_HASH_ALG","sha256");

Inside there is a tool that allow you to compute a new password hidden with a specific password field. So may be you make a first access with Bella Ciao and than compute the new one and overwrite the existing one.
At the first access it creates a new database, if you do not change anithing, will have the path  `qLite.sqlite3` and the name `main` . This database is the brain of qLite, here are saved all the users password, and all the path of the database managed by qLite. The table users, has three fields, user, that is the nickname, unique case insensitive, so JURHAS or jurhas  are exactly the same. Since there is a UNIQUE INDEX upper(user), remember that to allow the database to use this indexes in the query plan the WHERE clause must match this expression. But I do not think you are never going to have so much users or dbs to experiment performances problems. But I have to aware you.  Ever in the table users there is the field pwd, where is stored the crypted password. It is useless for "admin", his password is saved in the header as already seen,  "public" opposite problem, he do not need also to authenticate him self, and the groups. Using the postgres definition of group "the group is a user that cannot login" . So to simulate this behaviour just set pwd to a not valid sha256 value. I would suggest simply 'group', so you can filter easily. There is a further field, the day I felt generous, it is `lev`,  it gives you another way to select a group of users, may be 'superuser' and 'user', the logic is completely up to you. I did not use it in any place so you can assume every logic. To add a new user, use the query displayed bottom left insert->User.  The password displayed is also Bella Ciao. 
The second table is dbs, where are stored all the database paths and the name to display. A path can be C:/users/.../wp.sqlite3, meanwhile the name to display can be just wp. Inside the database we refer to the databases only with the name, stored in the field `db`. Therefore also db has his UNIQUE INDEX upper(db). Case insensitive. Path has no unique index , so there could be two different rows the points to the same database. If it scares you, create a unique index:  CREATE UNIQUE INDEX u_ix_dbs_path ON dbs(upper(path)); It is not completely bullet proof since an absolute path and a relative path can refer to the same db and no way to detect it. The last field is grp, it is the abbreviation of group, but I could not use it as name since it is a SQL key word. I think that with this basic settings you can manage some hunderds db, and of course we need to organize them. When grp is set the database will be showed inside a drop down menu with the title grp, meanwhile if grp is not set, it will be shown outside, to have a quicker access to them.
To insert/create a database with  qLite, you have to use the specific Admin->New Database that you can find in the Top-Right, where to insert the Local Path. This  will create a new one or add an existing one. Inside the new added database it will be created a table, qLite_qry, where the users can store their queryes. The queryes will be displayed on the left bottom of the control pannel, also here if grp is set, the query will be added inside a dropdown, meanwhile if not set outside. There is a UNIQUE (upper(name),upper(query)), that means that inside a group cannot exists two queries with the same name. It is not possible to exclude the creation of this table and to read it, if you want modify it you have to do it programmactically.
After you insert it, we change the name of the database, and you have to do with the query update->Database Name .Refear to it with the path, put a  db name. After this, you can forget the path. Now you have to refer to this database only with the name. The reason for this two-step solution, is the error handling, it is hard to understand what to do in case of error. 
We create a user, we create a database. Now we Grant a user to a database. An user can only access to the database where is granted. Also if a database is granted for public, the user must be granted. To do it, left-bottom  Grant->User To Db.  The SQLite database can be opened read-only or write, these are the only two options available, so  the field canwrite refer to the possibility of the user to create tables,  write, and make modification. A user should never be allowed to write in shared databases (also if he has to write). The canwrite must be set to 1 or to user that own their database for own purpose, or to trusted superuser. 

Now you can create how many database you want, how many users you want. But there is a problem. If the database serves a team, you cannot give to 10 persons the rights to write DELETE FROM table. If there is also just 2 persons that can do it, there are still 1.5 person too much. So repeat nobody can write.
We need a trap-door, where users cannot put their hands and work with defined queryes. The solution is the menu. The name comes from the classical menu bar of windows. From the menu, every user can write in each database, also if he has no access rights, he can write also in the main. Cause this power all the menus are stored inside the main, and only the admin, can grant them. Menus becomes therefore a way to put code inside our GUI and can serve different purpose. We will see few examples later. I am not going to convice you that this is the best solution, of course a specific application is better. But the costs for a specific application grow quickly and small team of 4-5 peoples cannot surely invest 80% of their time to develope a specific application. This would be justified only for big projects. But also if it is not the best solution, it has still some advantage: you can filter and sort in each way you want, and with a specific application this is not possible, also if you invest a lot of time.   
Now we have another problem. The menu works fine, but would be nice to select from a table the values and avoid typos and also save a bit time. Now the hack. The answer table has assigned a class which name is composed from the concatenation of the header. This means that if we write SELECT one,two, foo ...  the table has class="onetwofoo". 
Now we can detect if the user works with the correct query, and if he does, it is very simple fill the fields and update,delete or inherit and insert new ones.   
How do we crack this? Just use alias SELECT '10' one,'30' two, 'ed'  foo, you can force whatever you want. But surely a spreadsheets does not solves the problem. So this just a weird way to delete the item 30 clicking on 20. There is also the possibility to put a log, to discourage Neo to make the hack of the century.  Be carefull that if you use simple tables, id,name ... may match also other  tables that serves differently purposes. In this case enforce the header with alias and use more columns.

# Menu SQL

The table is the follow:
    CREATE TABLE IF NOT EXISTS  menus( 
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name TEXT NOT NULL CHECK(length(name)>0),
			isif INTEGER DEFAULT 0 NOT NULL,
			cof TEXT CHECK( iif(isif>0, cof IS NOT NULL AND length(cof)>0,1)), 
			nameof TEXT DEFAULT '',
			grp TEXT DEFAULT '',
			description TEXT,
			UID INTEGER,
			dateid TEXT);
We start with id, the uniquiness is granted for (name,grp), so we have to identify a menu with his id, otherwise becomes uncomfortable.
Name: is the name to be shown.
isif:  if 0  cof is  raw html/javascript code, if 1 cof is a function that get called before the submit.  You have to define it
cof: can be code or function accordingly to isif
nameof: NULL or the name of a function that get called after the submit. You have to define it. Not yet supported.
grp: grp here, and only here, accepts also paths,separated by a dot  for example: Tools.String . This will build a submenu. The number of menus can grow very quicly so we need better instruments. But it is still a bottle neck,  I think that just with few databases we are going to have duplicates, but I still didn't find an elegant solution that avoids this problem. So as C teach us, invent unique names,may be DBNAME_xxx, Operative Systems are written in this way. 
description: this is for your use, when you are going to have several menus you will need a briefly explanation, rather to read the whole code.
UID: not GUID, this is the way I identify my menus, so can I give you some support
dateid: the last change for the menu

Now we grant them: 
 
CREATE TABLE IF  NOT EXISTS mtodu( 
			id_m INTEGER NOT NULL,
			id_d INTEGER, 
			id_u INTEGER
   );
Despite his simplicity, it took me a couple of days to define his structure. Until I understood that there is no way to do all the job with a single query but we have to split the problem.
Of course id_m (menu) must be NOT NULL, he is our main charachter. But id_d(dbs) and id_u(users)... why not?
So there are 4 cases:
<ul>
	<li>id_d IS NULL, id_u IS NULL  The menu is available for all users and inside each database. Typically a safe tool</li>
	<li>id_d IS NULL, id_u=5  The menu is available over all, only for the id_u=5(an example). Typically super-user powers</li>
	<li>id_d=3, id_u IS NULL  The menu is available only inside a specific database for all granted user. Typically the write functions of this specific db</li>
	<li>id_d=3, id_u=5  The menu is available only inside a specific database for the specific user. Typically team-manager powers.</li>
</ul>
If you promote a menu from shared to reserved, remember to delete first the existing grant, because until exists this row the menu is shared. 
The user public,must be granted for each menu, also the safe tools.
Practically you can assign menus withs surgical precision, this is the true grant.

# Menu php/html

When we writing a menu that is going to submit we must know wich fields will be processed. The input that submits must have the name b1 or b2 or b3. You can easily increase this number adding rows to the variable `$submits`. With each one of these buttons is associated a query, respectively qry1,qry2,qry3. 
Now we define some macros
	array("s1","{s1}","s")  this will replace all occurrences inside qry(n) of {s1} with the value of the input named s1 formating it as text because s is specified
 	array("n1", "{n1}","n"), as above but n specificate that is a numeric value
  
an input named `wdb`, will open a different database and not the current.
if `lqry` is specified, it will execute a "log" query, this is his purpose but of course you can do wathever you want
if `ldb` is specified, it will execute the "log" query, in another database
a basic implementation so will be:

`<input type="submit" name="b1" value="Close">
<input type="submit" name="b2" value="Open">
<input type="hidden" name="qry1" value="UPDATE pag SET chiusa=1 WHERE id={n1}">
<input type="hidden" name="qry2" value="UPDATE pag SET chiusa=0 WHERE id={n1}">
<input type="hidden" id="n1"  name="n1" value="">`
So the first two input define two buttons, with the first one is binded the hidden qry1 and with the second qry2 .
The qry1 and qry2, contain the macro {n1}. This value will be replaced with the value of the last hidden input named n1. This will updated programmatically, when the user clicks on the table. 
So you do not have to write functions inside the database, and you can quickly perform write operations without occur in accidental UPDATE without WHERE. 
 
# Menu Javascript
The menu is written in Javascript. So we need some handlers.
At the load of the page it calls `on_doc_load()`, that you have to define if you need
The answer table has class composed from the concatenation of the headers (if valid of course, otherwise nothing, no way that can be a valid query) 
Each cell has class named after his own header. This can be a bit dangerous, can match existings classes, I use complex name, but I cannot give warranty, if some weird behaviour happen use an alias. 
With each cell is bounded a `on_tbl_clk(row,col)`, that you have to define. 
There is a div with id="serv_container" where there is a empty, and hidden textarea, the show up when get filled. If you need more controls, you need to add them programmatically. 
There is at the very-end a `div` with id `furthertable` where to show, other tables loaded with AJAX.
If you use AJAX, you have to do it with POST and you must set a variable named ajax. A query specified in a variable aqry and if you need different database you have to specificate it inside a aqry so a tipically AJAX request will be:

`
var par=`ajax=1&aqry=` + `SELECT par,row,col FROM par WHERE id_p=` + tbl.rows[r].cells[0].innerHTML ;
xhr.open(`POST`, `qLite.php`,true);
.`
We set the variable `ajax` we give a value not important what, we define `aqry` and than we open a request `POST`.
Up to you where to display it. 

A simple example:
My site: I want allow people to click on a link and open the relative wikipedia page. I must escape the query or people can write malicious code. So I cannot use link inside the query. I catched the column where is stored the link, that is named path, therefore his class is also path. The on_doc_load() defines the style to format the column as link,and comunicate people that this column is clickable. And when he clicks, it calls the function on_tbl_clk, I check if he clicked the right column and then I open the link consequently. 
`
	<script> function on_doc_load() 
	  { 
	  
		var styleSheet = document.createElement("style");
		styleSheet.innerHTML=".path td {color:#069;text-decoration: underline;cursor: pointer;}";
		document.body.appendChild(styleSheet);
		} 
	  
	  function on_tbl_clk(r,c) 
	  { 
		var tbl= document.getElementById("anstbl"); 
		if(tbl.rows[0].cells[c].innerHTML=="path") 
			window.open("https://it.wikipedia.org/wiki/" + tbl.rows[r].cells[c].innerHTML , "_blank"); } 
	</script>'
`
A complete example:
We define a button where we suggest the rigth query, the one that allows the users to work. The query is SELECT id,titolo,autore,chiusa, '' p ... so the class name will be idtitoloautorechiusap.
We define the inputs and the relative queryes.
Also here on_doc_load() defines the styles to allert people that Ok! this is the right query. 
In the on_tbl_clk() function we check first if the query is the right one: until this menu is selected does not matter if it is the right query or less, this function will be ever processed on click(), so if not we have to exit.  We toggle the  selected row, we fill the table to get a resume, and we open an ajax request. The same as above. And show further informations, after few attempts I found out that display inside the table, in the last cell is very confortable. So I put my answer there. And also here, if he clicks in a specific cell, will be readdressed to the wikipedia page.  

`<span style="position:relative; top:0%; left:0%; "> Get query<br></span>
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
	</script>`



# Step by Step Configuration

Ok probably until now is it not all clear. Now we are going to show a whole step by step configuration.

### The first login 
![FirstLogin](https://github.com/jurhas/qLite/assets/11569832/b2a8d099-415f-4c41-8abd-82a3727c8606)

If admin digits the wrong database, in this case is empty, surely wrong,  he will readressed to the main.<br>
The first password is Bella Ciao.<br>
The first login creates the main database.<br>

### Welcome on board

![Sha](https://github.com/jurhas/qLite/assets/11569832/fd4a3087-6703-43d7-b9d7-b23b649b7aaf)

Welcome inside qLite.<br>
From Top, clockwise:
<ul>
	<li>In red is shown the current menu. It is persistent also after a submit.</li>
	<li>If availables (not here), at the right of the current menu, are shown the quick-access menu</li>
	<li>Imediatly under it there is the menu lists.</li>
	<li>The Menu control panel, allows to create small masks. In this case we have the tool to compute the SHA256 of our new  password. There is no control, so admin must impose himself a strong password.</li>
	<li>At the bottom, hidden if empty, there is a &lt;textarea id="service" &gt;. This is a comode space where to display outputs, work outside the main textarea and prepare string</li>
	<li>The &lt;textarea id="service" &gt;, is inside a `&lt;div id= "serv_container"&gt;`,if the Menu Control Panel is not enough, here you can add other controls, ever inside the &lt;form&gt;, so if required they will also submit. </li>
	<li>Now there are a sequence of dropdowns, that are all the fields of the table `qLite_qry`. If the query is inside a dropdown, means that his `grp` is set, and is named for example "delete" or "insert"...  Clicking on it, be carefull, it will set the main textare with the query. So if there is another query, this will get lost.</li>
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

We write our database path. And click on Ok<br>
This operation will fail if:
<ul>
<li>The path is an existing file, but not a valid sqlite3 database. Let say a .txt file</li>
<li>The path points to a not existing folder. So let say foo/db.sqlite3, if foo does not exists it fails</li>
</ul>
If suceed the  new added database, will be shown on the right of our current database, but his name is still the path.<br>
![rename DB](https://github.com/jurhas/qLite/assets/11569832/2912cd26-fe52-4e2e-ab4e-112435b891c1)

We select the query, update->Database Name , 
![rename DB](https://github.com/jurhas/qLite/assets/11569832/1ccd356c-cb16-4cf8-8f02-3c94bbb37961)
We set the name in the field `db` and if required `grp` WHERE  db = the path
After you run the query, the new name will be imediatly available, and we will speak never again about the path. Now he is `db`.<br>

