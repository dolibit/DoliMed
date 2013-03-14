<?php
//==========================================================================
//
//Université de Strasbourg - Direction Informatique
//Auteur : Guilhem BORGHESI
//Création : Février 2008
//
//borghesi@unistra.fr
//
//Ce logiciel est régi par la licence CeCILL-B soumise au droit français et
//respectant les principes de diffusion des logiciels libres. Vous pouvez
//utiliser, modifier et/ou redistribuer ce programme sous les conditions
//de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA
//sur le site "http://www.cecill.info".
//
//Le fait que vous puissiez accéder à cet en-tête signifie que vous avez
//pris connaissance de la licence CeCILL-B, et que vous en avez accepté les
//termes. Vous pouvez trouver une copie de la licence dans le fichier LICENCE.
//
//==========================================================================
//
//Université de Strasbourg - Direction Informatique
//Author : Guilhem BORGHESI
//Creation : Feb 2008
//
//borghesi@unistra.fr
//
//This software is governed by the CeCILL-B license under French law and
//abiding by the rules of distribution of free software. You can  use,
//modify and/ or redistribute the software under the terms of the CeCILL-B
//license as circulated by CEA, CNRS and INRIA at the following URL
//"http://www.cecill.info".
//
//The fact that you are presently reading this means that you have had
//knowledge of the CeCILL-B license and that you accept its terms. You can
//find a copy of this license in the file LICENSE.
//
//==========================================================================

define("NOLOGIN",1);		// This means this output page does not require to be logged.
define("NOCSRFCHECK",1);	// We accept to go on this page from external web site.
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../dolibarr/htdocs/main.inc.php");     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (! $res && file_exists("../../../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (! $res) die("Include of main fails");
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");

include_once('../fonctions.php');
include_once('../bandeaux_local.php');
include_once('../creation_sondage.php');

$erreur = false;
$testdate = true;
$date_selected = '';



/*
 * Action
 */

// Set session vars
$erreur_injection = false;
if (isset($_SESSION["nbrecases"])) {
	for ($i = 0; $i < $_SESSION["nbrecases"]; $i++) {
		if (isset($_POST["choix"][$i])) {
			$_SESSION["choix$i"]=$_POST["choix"][$i];
		}
		if (isset($_POST["typecolonne"][$i])) {
			$_SESSION["typecolonne$i"]=$_POST["typecolonne"][$i];
		}
	}
} else { //nombre de cases par défaut
	$_SESSION["nbrecases"]=5;
}

if (isset($_POST["ajoutcases"]) || isset($_POST["ajoutcases_x"])) {
	$_SESSION["nbrecases"]=$_SESSION["nbrecases"]+5;
}

if (isset($_POST["confirmecreation"]) || isset($_POST["confirmecreation_x"]))
{
	//recuperation des données de champs textes
	$toutchoix = '';
	for ($i = 0; $i < $_SESSION["nbrecases"] + 1; $i++)
	{
		if (isset($_POST["choix"]) && issetAndNoEmpty($i, $_POST["choix"]))
		{
			$toutchoix.=',';
			$toutchoix.=str_replace(array(",","@"), " ", $_POST["choix"][$i]).(empty($_POST["typecolonne"][$i])?'':'@'.$_POST["typecolonne"][$i]);
		}
	}

	$toutchoix=substr("$toutchoix",1);
	$_SESSION["toutchoix"]=$toutchoix;

	if (issetAndNoEmpty('champdatefin')) {
		$registredate=explode("/",$_POST["champdatefin"]);
		if (is_array($registredate) === false || count($registredate) !== 3) {
			$testdate = false;
			$date_selected = $_POST["champdatefin"];
		} else {
			$time = mktime(0,0,0,$registredate[1],$registredate[0],$registredate[2]);
			if ($time === false || date('d/m/Y', $time) !== $_POST["champdatefin"]) {
				$testdate = false;
				$date_selected = $_POST["champdatefin"];
			} else {
				if (mktime(0,0,0,$registredate[1],$registredate[0],$registredate[2]) > time() + 250000) {
					$_SESSION["champdatefin"]=mktime(0,0,0,$registredate[1],$registredate[0],$registredate[2]);
				}
			}
		}
	} else {
		$_SESSION["champdatefin"]=time()+15552000;
	}

	if ($testdate === true) {
		//format du sondage AUTRE
		$_SESSION["formatsondage"]="A".$_SESSION["studsplus"];

		ajouter_sondage();
	} else {
		$_POST["fin_sondage_autre"] = 'ok';
	}
}




/*
 * View
 */

$form=new Form($db);

$arrayofjs=array('/opensurvey/block_enter.js');
$arrayofcss=array('/opensurvey/css/style.css');
llxHeaderSurvey($langs->trans("OpenSurvey"), "", 0, 0, $arrayofjs, $arrayofcss);

if (issetAndNoEmpty('titre', $_SESSION) === false || issetAndNoEmpty('nom', $_SESSION) === false || issetAndNoEmpty('adresse', $_SESSION) === false)
{
	dol_print_error('',"You haven't filled the first section of the poll creation");
	llxFooterSurvey();
	exit;
}


//partie creation du sondage dans la base SQL
//On prépare les données pour les inserer dans la base

echo '<form name="formulaire" action="#bas" method="POST" onkeypress="javascript:process_keypress(event)">'."\n";
echo '<div class="bandeautitre">'. $langs->trans("CreatePoll")." (2 / 2)" .'</div>'."\n";

echo '<div class=corps>'."\n";
echo '<br>'. $langs->trans("PollOnChoice") .'<br><br>'."\n";
echo '<table>'."\n";

//affichage des cases texte de formulaire
for ($i = 0; $i < $_SESSION["nbrecases"]; $i++) {
	$j = $i + 1;
	if (isset($_SESSION["choix$i"]) === false) {
		$_SESSION["choix$i"] = '';
	}
	echo '<tr><td>'. $langs->trans("TitleChoice") .' '.$j.' : </td><td><input type="text" name="choix[]" size="40" maxlength="40" value="'.dol_escape_htmltag($_SESSION["choix$i"]).'" id="choix'.$i.'">';
	$tmparray=array('checkbox'=>$langs->trans("CheckBox"),'yesno'=>$langs->trans("YesNoList"),'pourcontre'=>$langs->trans("PourContreList"));
	print ' &nbsp; '.$langs->trans("Type").' '.$form->selectarray("typecolonne[]", $tmparray, $_SESSION["typecolonne$i"]);
	echo '</td></tr>'."\n";
}

echo '</table>'."\n";

//ajout de cases supplementaires
echo '<table><tr>'."\n";
echo '<td>'. $langs->trans("5MoreChoices") .'</td><td><input type="image" name="ajoutcases" value="Retour" src="images/add-16.png"></td>'."\n";
echo '</tr></table>'."\n";
echo'<br>'."\n";

echo '<table><tr>'."\n";
echo '<td></td><td><input type="submit" class="button" name="fin_sondage_autre" value="'.dol_escape_htmltag($langs->trans("NextStep")).'" src="images/next-32.png"></td>'."\n";
echo '</tr></table>'."\n";

//test de remplissage des cases
$testremplissage = '';
for ($i=0;$i<$_SESSION["nbrecases"];$i++) {
	if (isset($_POST["choix"]) && issetAndNoEmpty($i, $_POST["choix"])) {
		$testremplissage="ok";
	}
}

//message d'erreur si aucun champ renseigné
if ($testremplissage != "ok" && (isset($_POST["fin_sondage_autre"]) || isset($_POST["fin_sondage_autre_x"]))) {
	print "<br><font color=\"#FF0000\">" . $langs->trans("Enter at least one choice") . "</font><br><br>"."\n";
	$erreur = true;
}

//message d'erreur si mauvaise date
if ($testdate === false) {
	print "<br><font color=\"#FF0000\">" . _("Date must be have the format DD/MM/YYYY") . "</font><br><br>"."\n";
}

if ($erreur_injection) {
	print "<font color=#FF0000>" . _("Characters \" < and > are not permitted") . "</font><br><br>\n";
}

if ((isset($_POST["fin_sondage_autre"]) || isset($_POST["fin_sondage_autre_x"])) && !$erreur && !$erreur_injection) {
	//demande de la date de fin du sondage
	echo '<br>'."\n";
	echo '<div class=presentationdatefin>'."\n";
	echo '<br>'. _("Your poll will be automatically removed after 6 months.<br> You can fix another removal date for it.") .'<br><br>'."\n";
	echo _("Removal date (optional)") .' : <input type="text" name="champdatefin" value="'.$date_selected.'" size="10" maxlength="10"> '. _("(DD/MM/YYYY)") ."\n";
	echo '</div>'."\n";
	echo '<div class=presentationdatefin>'."\n";
	echo '<font color=#FF0000>'. $langs->trans("InfoAfterCreate") .'</font>'."\n";
	echo '</div>'."\n";
	echo '<br>'."\n";
	echo '<table>'."\n";
	echo '<tr><td>'. $langs->trans("CreatePoll") .'</td><td><input type="image" name="confirmecreation" src="images/add.png"></td></tr>'."\n";
	echo '</table>'."\n";
}

//fin du formulaire et bandeau de pied
echo '</form>'."\n";


echo '<a name=bas></a>'."\n";
echo '<br><br><br>'."\n";
echo '</div>'."\n";

llxFooterSurvey();

$db->close();

?>