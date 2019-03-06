/*
select 'Textos Coletados' as 'HEADER', max(idtexto) as 'INFO' from texto union select 'Autores Coletados',max(idautor) from autor  union select 'Pagina Atual' as `?`,max(pagina) as 'Info'  from texto where idautor = (select max(idautor) from texto)  union select 'Ultima Pagina',limite from autor where idautor =(select max(idautor) from texto) union select 'Autor Coletado', nome from autor where idautor = (select max(idautor) from texto) union select 'Autor Mapeado', nome from autor where idautor = (select max(idautor) from autor);

show create table texto;

| texto | CREATE TABLE `texto` (
  `idtexto` int(11) NOT NULL AUTO_INCREMENT,
  `texto` longtext,
  `idautor` int(11) NOT NULL,
  `pagina` int(11) DEFAULT NULL,
  PRIMARY KEY (`idtexto`)
) ENGINE=InnoDB AUTO_INCREMENT=84278 DEFAULT CHARSET=latin1 |

show create table autor;

| autor | CREATE TABLE `autor` (
  `idautor` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) DEFAULT NULL,
  `limite` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idautor`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=4143 DEFAULT CHARSET=latin1 |

*/


<?php
header("Content-type: text/html; charset=utf-8");



get_textos();

	function get_autor(){

		$abc = ['z','y','x','w'];
		foreach($abc as $letter){

			$url = file_get_contents("http://ppronhub.com/autores/$letter/");
			$search = '/(<li><a href="\/autor)(.*)(<\/a><\/li>)/';
			$out = array();
			preg_match_all($search, $url, $out, PREG_PATTERN_ORDER);
			$out = explode('<li>',$out[0][1]);
			$autores = array();
			foreach($out as $value){
			
				$link = explode('"',$value)[1];//)"
				$link = explode('/',$link)[2];
				if($link == '') continue;
				$nome = explode('>',$value)[1];//)"
				$nome = explode('<',$nome)[0];
				$autor_url = file_get_contents("http://pornhub.com/ator/$link/");
				$search = '/(<strong>)([0-9]*)(<\/strong>)/';
				$out = array();
				preg_match_all($search, $autor_url, $out, PREG_PATTERN_ORDER);
				$limite = ceil($out[2][0]/25);
				select("insert into autor values(default,'$nome','$limite','$link',null);");
				//array_push($autores,['nome' => $nome,'url' => $link,'limite' => $limite]);   
			
			}	
		}
	}



	function get_textos(){
		$max_id_autor = select("select max(idautor) from autor;")[0]['max(idautor)'];
		
		for($id_autor = 2700; $id_autor <= select("select max(idautor) from autor;")[0]['max(idautor)'];$id_autor++){
			$id_autor = $id_autor == 2701?3156:$id_autor;
			$autor = select("select * from autor where idautor = $id_autor;")[0];
			$url_autor = $autor['url'];
			$limite = $autor['limite'];
			
			for($i = 1;$i < $limite;$i++){
			
				$url = file_get_contents('http://pornhub.com/'.$url_autor.'/'.$i);
				$search = '/(<div class="thought-card">)(.*)(<\/p>)/';
				$out = array();
				preg_match_all($search, $url, $out, PREG_PATTERN_ORDER);
				foreach($out as $value)
					foreach($value as $words){
						$strip_word = strip_tags($words);
						if($strip_word == '') continue;
						select("insert into texto values (default,'$strip_word',$id_autor,$i);");            
					}  
			}
		}
	}

	function start(){
		return new \PDO(
			"mysql:dbname=frase".";host=localhost",
			"root",
			"root"
		);
	}

	 function setParams($statement, $parameters = array())
	{
		foreach ($parameters as $key => $value) {
			bindParam($statement, $key, $value);
		}
	}

     function bindParam($statement, $key, $value)
	{
		$statement->bindParam($key, $value);
	}

     function query($rawQuery, $params = array())
	{
        $conn = start();
        $conn->exec("set names utf8");
		$stmt = $conn->prepare($rawQuery);		
		setParams($stmt, $params);
		$stmt->execute();
	}

	 function select($rawQuery, $params = array())
	{
        $conn = start();
        $conn->exec("set names utf8");
		$stmt = $conn->prepare($rawQuery);		
		setParams($stmt, $params);
		$stmt->execute();
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

?>
