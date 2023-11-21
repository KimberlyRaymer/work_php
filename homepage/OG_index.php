<?php
	$dbh=new PDO("mysql:host=localhost;dbname=soybase",'www','');
	$str='';
	$dlstr1=$dlstr2="http://soybase.org/correspondence/methods.txt\n\n";
	//
	// NEED TO UPDATE THIS ARRAY WHEN WE GET A NEW ASSEMBLY !!!!!
	//
	$vtranslate=array();
	$vtranslate['Glyma 1.0']='Wm82.a1.v1';
	$vtranslate['Glyma 1.1']='Wm82.a1.v1.1';
	$vtranslate['Glyma1.1']='Wm82.a1.v1.1';
	$vtranslate['Glyma2.0']='Wm82.a2.v1';
    $vtranslate['Wm82.a4.v1']='Wm82.a4.v1';
    
	$examples=array('Glyma01g26650','Glyma01g41630','Glyma02g02990','Glyma02g15220','Glyma03g13886','Glyma05g25840','Glyma06g06460','Glyma06g18800','Glyma07g04930','Glyma07g06270','Glyma07g09800','Glyma07g16080','Glyma11g00640','Glyma11g10140','Glyma12g05450','Glyma12g06910','Glyma12g36150','Glyma13g42560','Glyma13g43730','Glyma15g40120','Glyma17g35423','Glyma20g28230','Glyma15g39924','Glyma15g20740','Glyma13g00700');
	//check to see if a file upload occured, if so put contents into $pload
	if(isset($_FILES['file_upload']['tmp_name'])&&is_file($_FILES['file_upload']['tmp_name'])){
		$pload=file_get_contents($_FILES['file_upload']['tmp_name']);
	}
	// check to see if a list was entered into the $featurelist or into $pload
	if(isset($_REQUEST['featurelist'])&&strlen(trim($_REQUEST['featurelist']))>0||isset($pload)&&strlen(trim($pload))>0){
		// if $pload has something in it explod into $glist
		if(isset($pload)&&strlen(trim($pload))>0){
			$glist=explode("\n",trim($pload));
		}else{
			// else explode featurelist into $glist
			$glist=explode("\n",trim($_REQUEST['featurelist']));
		}
		// check to see if this is a debug request, if so print out $glist
		if(isset($_REQUEST['debug'])){echo "<pre>".__LINE__.":glist\n";print_r($glist);echo "</pre>";}
		$return=$versions=array();
		//get a list of all feature_source versions for table headings
		$q="SELECT DISTINCT feature_source_version FROM soybase.sequence_feature_similog_table";
		$dbh->quote($q);
		$r=$dbh->prepare($q);
		$r->execute();
		$return=$versions=array();
		if($r->rowCount()>0){
			foreach($r AS $l){
				$versions[trim($l[0])]=trim($l[0]);
			}
		}
		sort($versions);
		//check to see if this is a debug request
		if(isset($_REQUEST['debug'])){echo "<pre>".__LINE__.":versions\n";print_r($versions);echo "</pre>";}
		//compose general request
		$q="SELECT feature_name,feature_source_version,similog_name,target_source_version FROM soybase.sequence_feature_similog_table WHERE similog_type='correspondence' AND feature_name IN(";
		//go through list of genes requested and add to the SQL request 
		foreach($glist AS $gno=>$glyma){
			$q.="'".ucwords(strtolower(trim($glyma)))."'";
			if(isset($glist[$gno+1])){$q.=",";}
		}
		$q.=") AND feature_name!='no correspondence' AND similog_name!='no correspondence' AND similog_name!='no_correspondence'";
		$dbh->quote($q);
		$r=$dbh->prepare($q);
		$r->execute();
		if($r->rowCount()>0){
			foreach($r AS $l){
				$return[trim($l[0])][trim($l[1])][trim($l[0])]=trim($l[0]);
				$return[trim($l[0])][trim($l[3])][trim($l[2])]=trim($l[2]);
				$q2="SELECT feature_name,feature_source_version FROM soybase.sequence_feature_similog_table WHERE similog_name='".$l[2]."' AND similog_type='correspondence'";
				$dbh->quote($q2);
				$r2=$dbh->prepare($q2);
				$r2->execute();
				if($r2->rowCount()>0){
					foreach($r2 AS $l2){
						$return[trim($l[0])][($l2[1])][trim($l2[0])]=trim($l2[0]);
					}
				}
				
			}
		}
		if(isset($_REQUEST['debug'])){echo "<pre>".__LINE__.":return\n";print_r($return);echo "</pre>";}
		foreach($glist AS $gno=>$glyma){
			if(!isset($return[ucwords(strtolower(trim($glyma)))])){
				$q="SELECT feature_name,feature_source_version FROM soybase.sequence_feature_table WHERE feature_name='".ucwords(strtolower(trim($glyma)))."'";
				$dbh->quote($q);
				$r=$dbh->prepare($q);
				$r->execute();
				if($r->rowCount()>0){
					foreach($r AS $l){
						$return[trim($l[0])][trim($l[1])][trim($l[0])]=trim($l[0]);
					}
				}
			}
		}
		if(isset($_REQUEST['debug'])){echo "<pre>".__LINE__.":return\n";print_r($return);echo "</pre>";}
		//Clean Return
		$rfvcount=array();
		if(count($return)>0){
		foreach($return AS $fname=>$stuff1){
			if(count($stuff1)>0){
				foreach($stuff1 AS $verid=>$pairsoffnames){
					if(count($pairsoffnames)>0){
						$rfvcount[$verid][$fname]=count($pairsoffnames);
					}
				}
			}	
		}
		}
		$tbl='';
		$tbl.="<tr><th>Submitted<br />Feature</th>";
		$dlstr1.="Submitted Feature";
		//make headers for output table from $versions
		foreach($versions AS $version){
		//use the version translation array vtranslate to get the proper version string
			$tbl.="<th>".$vtranslate[$version]."</th>";
			$dlstr1.="\t".$vtranslate[$version];
		}
		$dlstr1.="\n";
		$tbl.="</tr>";
		//end header row
		foreach($glist AS $glyma){
			$tbl.="<tr><td style='background-color:#C0C0C0;padding-right:2em;vertical-align:top;'>".ucwords(strtolower(trim($glyma)))."</td>";
			//format $glyma to have Glyma and lower case g
			$dlstr1.=ucwords(strtolower(trim($glyma)));
			if(isset($return[ucwords(strtolower(trim($glyma)))])){
				foreach($versions AS $version){
					$tbl.="<td style='vertical-align:top;'>";
					if(isset($return[ucwords(strtolower(trim($glyma)))][trim($version)])&&count($return[ucwords(strtolower(trim($glyma)))][trim($version)])>0&&$rfvcount[trim($version)][ucwords(strtolower(trim($glyma)))]>0){
						$dlstr1.="\t";
						foreach($return[ucwords(strtolower(trim($glyma)))][trim($version)] AS $val){
//							if(trim($val)!=='no correspondence'){
							$dlstr1.=$val." ";
							$tbl.=$val."<br />";
//							}
						}
					}else{
						$dlstr1.="\tno correspondence";
						$tbl.="no correspondence";
					}
					$tbl."</td>";
				}
			}
			$dlstr1.="\n";
			$tbl."</tr>";
		}
		$filename1="./tmp/Glyma_Correspondence_Report_".date("YmdGis").".csv";
		$fp=fopen($filename1,'w');
		fwrite($fp,$dlstr1);
		fclose($fp);
			$str.="<br /><p>Instances where there is no reported correspondence between genome assemblies are indicated.</p><table id='reporttable'><tr>";
		if(isset($tbl)&&strlen(trim($tbl))>0){
			$colcount=count($versions)+1;
			$str.="<td style='vertical-align:top;padding-right:2em;border:none;'><a href='$filename1'>Download Correspondence Report</a><br />";
			$str.="<table style='border-collapse:collapse;'><tr><th colspan='$colcount' style='border:solid;'>Gene Model Name Correspondence</th></tr>";
			$str.=$tbl;
			$str.="</table>";
			$str.="</td>";
		}
		$str.="</tr></table>";
		}else{
		$str='';
		$str.="<p style='padding-left:20em;width:35em;text-align:justify;margin:1em;font-size:11pt;' id='splainintext'>";
		$str.="<span style='font-style:italic;'>The Phytozome Annotation Group</span> has released an updated assembly for
the <span style='font-style:italic;'>Williams 82 Genomic Sequence.</span><br /><br />
The genome sequence and gene models
have been substantially improved in the latest release, and are now
the defaults used at <a href='/index.php'>SoyBase</a>. However, one consequence of this is
that the new gene models are sometimes substantially different from
the cognates in previous annotations. To differentiate the various
genome assemblies and annotations a new nomenclature has been adopted
and the genes annotated to the new genome assembly have been named
using this style.<br /><br />

In short, the new nomenclature makes these changes:<br /><br />

A dot (i.e. period character) now separates the GenusSpecies prefix from the rest of the name.<br />
The number of digits after the 'g' is now 6 and steps between genes are now 100.<br />
Assembly and annotation info are now included in ID names.<br />
<br />
For example, for Williams 82 assembly version 2 annotation version 1<br />
Locus:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Glyma.01g000100<br />
Locus&nbsp;ID:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Glyma.01g000100.Wm82.a2.v1<br />
<br />
Transcript:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Glyma.01g000100.1<br />
Transcript&nbsp;ID:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Glyma.01g000100.1.Wm82.a2.v1<br />
<br />
Although the names have changed to reflect the new nomenclature,
<strong>48606 of the 56044 Wm82.a2.v1 gene models can be unambiguously
matched to those in the previous Glyma1.1 annotation</strong>. This page
provides a tool that accepts a list of gene names and returns a table
of correspondences as provided by <a href='http://www.jgi.doe.gov/'>JGI</a>.<br />
<br />
To use this tool, paste a list of gene model names into the text box
or upload the list and click the Submit button. The gene model list can contain either names from Wm82.a1, Wm82.a2 or a mix of the two. A file will be
prepared ready for download to your computer.<br />
<br />
<span style='text-decoration:underline;font-weight:bold;'>Alternately you may download the full correspondence files.</span><br /><br />
<a href='./full.php'>Download complete Glyma1.1<> Wm82.v2.a1 correspondence list</a><br><br>
<a href='./full.A4.php'>Download complete Wm82.a4.v1 correspondence list</a>";
		$str.="</p>";
		$str.="<form enctype='multipart/form-data' method='POST' id='inputform' style='width:15em;margin-top:-40em;'>
			<fieldset>
				<legend>Insert Gene List:<br /><span style='font-size:6pt;font-family:monospace;'>(One per line)</span></legend><br />
		<div style='font-size:9pt;width:18em;' id='instructionpanel'><h1 style='text-decoration:underline;padding:0;margin:0;line-height:0.5em;'>Instructions</h1><br />Enter a list of Wm82.a1 or Wm82.a2 gene model names into this box, one name per line.<br />
		Alternatively a pre-made list can be loaded by clicking on the green \"Click to Load From File\" text below.</div>
				<div style='visibility:hidden;font-size:0;height:0;line-height:0;' id='warning'>Please Provide<br />a List of Features<br />to Continue</div>
				<textarea rows='20' name='featurelist' id='featurelist' style='height:20em;width:12em;' onfocus='if(this.value.length>0){document.getElementById(\"warning\").style.cssText=\"visibility:hidden;font-size:0;height:0;line-height:0;\";this.style.backgroundColor=\"rgb(255,255,255)\";}'></textarea><br />
				<label for='fupload' style='font-size:10pt;color:#060;text-decoration:underline;cursor:pointer;' onclick='if(document.getElementById(\"fupload\").style.visibility==\"hidden\"){document.getElementById(\"fupload\").style.cssText=\"\";this.innerHTML=\"Load From File\";}else{document.getElementById(\"fupload\").style.cssText=\"height:0;visibility:hidden;line-height:0;\";this.innerHTML=\"Click to Load From File\";}'>Click to Load From File</label><br />
				<input type='file' id='fupload' size='10' name='file_upload' style='height:0;visibility:hidden;line-height:0;' /><br /><br />
				<button onmousedown='setup();' onclick='report_gen();return false;'>Submit</button><br /><br />
				<span onclick='document.getElementById(\"featurelist\").value=thelist;' style='text-decoration:underline;cursor:pointer;font-size:10pt;'>Click for Example Data</span>
			</fieldset>
		</form>";
	}	
?> 

<!-- HTML file begins -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>SoyBase.org - Gene Model Correspondence Lookup</title>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta name="description" content="SoyBase, the USDA-ARS Soybean Genetics and Genomics Database. This database contains genetic and genomic data for
		soybean, Glycine max and related species.  Data includes soybean gene calls, gene sequences, Affymetrics SoyChip probe sequence, soybean transposeable
		elements, soybean chromosome sequences and genetic and sequence maps" />
		<link rel="stylesheet" type="text/css" href="../include/sb_styles.css" />
		<link rel="stylesheet" type="text/css" href="../style/default.css" />
		<? include_once("../include/include.php"); ?>
		<link rel="alternate" type="application/rss+xml" href="/news/rss.php" title="RSS news feed" />
		<script>
			//To fill the text area box in "insert gene list" container
			var thelist="<?php foreach($examples AS $example){echo trim($example)."\\n";} ?>";
			var xmlHttp;
			var gdeview;
			var preloaded;
			var lst;
			var flist;
			<?php if(isset($pload)&&strlen($pload)>0){ ?>var preloaded="<?php echo $pload; ?>";<?php echo "\n"; } ?>
		</script>
		<style>
			iframe{padding:0;margin:0;border:0;}
			tr,td{border:solid;}
		</style>
		<!-- Does not exist (DNE) -->
		<?php include_once("../include/google_analytics2.php"); ?>
	</head>
	<!-- This is to create output list (body) -->
	<body onload='if(document.getElementById("featurelist").value.length>0){document.getElementById("toc").style.visibility="visible";document.getElementById("toc").style.lineHeight="";report_gen();}else{document.getElementById("toc").style.visibility="hidden";document.getElementById("toc").style.lineHeight="0";for(var i=0;i<=document.getElementsByClassName("sec_head").length;i++){if(document.getElementsByClassName("sec_head")[i]){document.getElementsByClassName("sec_head")[i].style.visibility="hidden";}}}'><a name='top'></a>
				<? include("../include/NewHeadSoy.txt"); ?>
		<noscript>	<?php include("../include/nojsscript.txt") ?> </noscript>
		<div class='sb_middle2'>
			<div class='sb_main'>
				<div class='top_bar' style='padding-right:2em;white-space:nowrap;'>Gene Model Correspondence Lookup</div>
				<?php if(isset($str)&&strlen(trim($str))>0){echo $str;} ?>
			</div>
		</div>
		<!-- build_bottom is in include.php -->
		<div class="sb_bottom" style='margin-top:48em;'>
			<? build_bottom(); ?>
		</div>
	</body>
</html>
