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
I was boting the italian Wikipedia dump, when my bot crashes. After few investigation comes out that there was some parenthesis to fix. This time I was not guilty. I scanned again the dump and I detect all the parenthesis, and I want to give them a relative big file to help them to mantein the wiki . And here comes the problem: how do I give them a structured filed? my site barely allow me to access to my database, Access... No! Access No!, but also if I would use it there is no way to allow people sparsed in Italy to work on the same db. SQLite requires experencied users, you need a shell or a GUI( matter of taste), and also here no whay to share server access... 
So... hold my beer...qLite is born.
The name is not obtained from cropping the S, in SQLite, in this case would be capital. The q is dedicate to the C function qsort(). My beloved language and the language which 
SQLite is written.qsort() is one of my prefered functions among with memset(), each time I use qsort() I am dumbily happy, never understood why. The q in qsort() means "quick", so "qLite"  would mean quick [&] Lite. That match pefectly with this project. Ok it is pervert enough: takken.




