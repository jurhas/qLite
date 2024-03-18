INSERT INTO menus (name,isif,cof,nameof,grp)
VALUES ('wiki_p',0,'<span style="position:relative; top:0%; left:0%; "> Get query<br></span>
	<button  type="button"  onclick="javascript:set_val(`str`,`SELECT id,titolo,autore,chiusa, '''' p 
	FROM pag 
	WHERE chiusa=0 
	ORDER BY id;`);">Get Query</button> 
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
		styleSheet.innerHTML=`.idtitoloautorechiusap  {float:left;}  
							.idtitoloautorechiusap tr {color:#069;text-decoration: underline;cursor: pointer; } 
							.selRow,
							.anstbl .selRow:nth-child(odd)  {background-color:#99EDE2;}`;
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
			var par=`ajax=1&aqry=` + `SELECT par,row,col FROM par WHERE id_p=` + tbl.rows[r].cells[0].innerHTML 
			+ ` ORDER BY row,col`;
			
		
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
	</script>',NULL,'');