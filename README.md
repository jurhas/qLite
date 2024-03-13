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
Target are internet community that works on the same project. Companies that work with small-medium project. And everywhere several users requires to share a common database.

# History

I briefely tell the history of this project. 
I was boting the italian Wikipedia dump, when my bot crashes. After few investigation I found out that there was some parenthesis to fix. This time I was not guilty. I scanned again the dump and I detect all the parenthesis, and I want to give them a relative big file to help them to mantein the wiki . And here comes the problem: how do I give them a structured filed? my site barely allow me to access to my database, Access... No! Access No!, but also if I would use it there is no way to allow people sparsed in Italy to work on the same db. SQLite requires experencied users, you need a shell or a GUI( matter of taste), and also here no whay to share server access... 
So... hold my beer...qLite is born.
The name is not obtained from cropping the S, in SQLite, in this case would be capital. The q is dedicate to the C function qsort(). My beloved language and the language which 
SQLite is written.qsort() is one of my prefered functions among with memset(), each time I use qsort() I am dumbily happy, never understood why. The q in qsort() means "quick", so "qLite"  would mean quick [&] Lite. That match pefectly with this project. Ok it is pervert enough: takken.

# Structure
The journey begins with the download of the file qLite.php. It is the only thing required. Put inside you xampp folder or in your website. And open it typing simple his name. So localhost/qLite.php  or yourwebsite.com/qLite.php. If you use XAMPP, be careful that SQLite is not available to default with php. So in Windows you have to go to the php.ini file and decoment extension=sqlite3 and pdo_sqlite. Restart apache. And now should be available. Also in Linux you have to include these extension, you have to install the php extension, chown the folder and give write rights to others with chmod o+w <folder> .This last step is hard to find in a online documentation and took me several hours to understand what was wrong.
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
We need a trap-door, where users cannot put their hands and work with defined queryes. The solution is the menu. The name comes from the classical menu bar of windows. From the menu, every user can write in each database, also if he has no access rights, he can write also in the main. Cause this power all the menus are stored inside the main, and only the admin, can grant them. Menus becomes therefore a way to put code inside our GUI and can serve different purpose. We will see few examples later. I am not going to convice you that this is the best solution, of course a specific application is better. But the costs for a specific application grow quickly and small team of 4-5 peoples cannot surely invest 80% of their time to develope a specific application. This would be justified only for big projects. But also if it is not a the best solution, it has still some advantage. You can filter and sort in each way you want, and with a specific application this is not possible, also if you invest a lot of time.   
Now we have another problem. The menu works fine, but would be nice to select from a table the values and avoid typos and also save a bit time. Now the hack. The answer table has assigned a class which name is composed from the concatenation of the header. This means that if we write SELECT one,two, foo ...  the table has class="onetwofoo". 
Now we can detect if the user works with the correct query, and if he does, it is very simple fill the fields and update,delete or inherit and insert new ones.   
How do we crack this? Just use alias SELECT '10' one,'30' two, 'ed'  foo, you can force whatever you want. But surely a spreadsheets does not solves the problem. So this just a weird way to delete the item 30 clicking on 20. There is also the possibility to put a log, to discourage Neo to make the hack of the century.  Be carefull that if you use simple tables, id,name ... can have clone and match  tables that serves differently purposes. In this case enforce the header with alias and use more columns.

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
isif:  if 0  cof is  raw html/javascript code, if 1 cof is a function that get be called before the submit.  You have to define it
cof: can be code or function accordingly to isif
nameof: NULL or the name of a function that get called after the submit. You have to define it. Not yet supported.
grp: grp here, and only here, accepts also paths  for example: Tools.String . This will build a submenu. The number of menus can grow very quicly so we need better instruments. 
But it is still a bottle neck,  I think that just with few databases we are going to have clones, but I still didn't find an elegant solution that avoids this problem. So as C teach us, invent unique names, Operative Sy.stems are written in this way. 
description: this is for your use, when you are going to have several menus you will need a briefly explanation, rather to read the whole code.
UID: not GUID, this is the way I identify my menus, so can I give you some support
dateid: the last change for the menu

Now we grant them: 

CREATE TABLE IF  NOT EXISTS mtodu( 
			id_m INTEGER NOT NULL,
			id_d INTEGER, 
			id_u INTEGER
   );
Despite his simplicity, it took me a couple of days to define his structure. Until I understood that there is no way to do all the job with a single query but we have to split the problem .

# Menu php
  The table where is stored the menu is 
Since the menu is writted on Javascript, he needs few handlers inside the document let see them:
<ul>
  <li></li>
</ul>




