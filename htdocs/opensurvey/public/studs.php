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
dol_include_once("/opensurvey/class/opensurveysondage.class.php");

include_once('../bandeaux_local.php');
include_once('../fonctions.php');

// Le fichier studs.php sert a afficher les résultats d'un sondage à un simple utilisateur.
// C'est également l'interface pour ajouter une valeur à un sondage deja créé.
$numsondage = false;

//On récupère le numéro de sondage par le lien web.
if(issetAndNoEmpty('sondage', $_GET) === true) {
	$numsondage = $_GET["sondage"];
	$_SESSION["numsondage"] = $numsondage;
}

if(issetAndNoEmpty('sondage') === true) {
	$numsondage = $_POST["sondage"];
	$_SESSION["numsondage"] = $numsondage;
} elseif(issetAndNoEmpty('sondage', $_COOKIE) === true) {
	$numsondage = $_COOKIE["sondage"];
} elseif(issetAndNoEmpty('numsondage', $_SESSION) === true) {
	$numsondage = $_SESSION["numsondage"];
}

if ($numsondage !== false) {
	$dsondage = get_sondage_from_id($numsondage);
	if($dsondage === false) {
		$err |= NO_POLL;
	}
} else {
	$err |= NO_POLL_ID;
}



/*
 * Actions
 */

// Add comment
if (isset($_POST['ajoutcomment']) || isset($_POST['ajoutcomment_x']))
{
	if (isset($_SESSION['nom'])) {
		// Si le nom vient de la session, on le de-htmlentities
		$comment_user = html_entity_decode($_SESSION['nom'], ENT_QUOTES, 'UTF-8');
	} elseif(issetAndNoEmpty('commentuser')) {
		$comment_user = $_POST["commentuser"];
	} elseif(isset($_POST["commentuser"])) {
		$err |= COMMENT_USER_EMPTY;
	} else {
		$comment_user = _('anonyme');
	}

	if(issetAndNoEmpty('comment') === false) {
		$err |= COMMENT_EMPTY;
	}

	if (isset($_POST["comment"]) && !is_error(COMMENT_EMPTY) && !is_error(NO_POLL) && !is_error(COMMENT_USER_EMPTY)) {
		// protection contre les XSS : htmlentities
		$comment = htmlentities($_POST['comment'], ENT_QUOTES, 'UTF-8');
		$comment_user = htmlentities($comment_user, ENT_QUOTES, 'UTF-8');

		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'opensurvey_comments (id_sondage, comment, usercomment) VALUES ('.
			$connect->Param('id_sondage').','.
			$connect->Param('comment').','.
			$connect->Param('comment_user').')';

		$sql = $connect->Prepare($sql);

		$comments = $connect->Execute($sql, array($numsondage, $comment, $comment_user));

		if ($comments === false) {
			$err |= COMMENT_INSERT_FAILED;
		}
	}
}


// Add vote
$sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'opensurvey_user_studs WHERE id_sondage='.$connect->Param('numsondage').' ORDER BY id_users';
$sql = $connect->Prepare($sql);
$user_studs = $connect->Execute($sql, array($numsondage));

$nbcolonnes = substr_count($dsondage->sujet, ',') + 1;
if (!is_error(NO_POLL) && (isset($_POST["boutonp"]) || isset($_POST["boutonp_x"])))
{
	//Si le nom est bien entré
	if (issetAndNoEmpty('nom') === false) {
		$err |= NAME_EMPTY;
	}

	if(!is_error(NAME_EMPTY) && (!isset($_SERVER['REMOTE_USER']) || $_POST["nom"] == $_SESSION["nom"]))
	{
		$nouveauchoix = '';
		for ($i=0;$i<$nbcolonnes;$i++)
		{
			if (isset($_POST["choix$i"]) && $_POST["choix$i"] == '1')
			{
				$nouveauchoix.="1";
			}
			else if (isset($_POST["choix$i"]) && $_POST["choix$i"] == '2')
			{
				$nouveauchoix.="2";
			}
			else { // sinon c'est 0
				$nouveauchoix.="0";
			}
		}

		$nom=substr($_POST["nom"],0,64);

		// protection contre les XSS : htmlentities
		$nom = htmlentities($nom, ENT_QUOTES, 'UTF-8');

		while($user = $user_studs->FetchNextObject(false)) {
			if ($nom == $user->nom) {
				$err |= NAME_TAKEN;
			}
		}

		// Ecriture des choix de l'utilisateur dans la base
		if (!is_error(NAME_TAKEN) && !is_error(NAME_EMPTY)) {

			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'opensurvey_user_studs (nom,id_sondage,reponses) VALUES ('.
				$connect->Param('nom').', '.
				$connect->Param('numsondage').', '.
				$connect->Param('nouveauchoix').')';
			$sql = $connect->Prepare($sql);

			// Todo : Il faudrait lever une erreur en cas d'erreur d'insertion
			$connect->Execute($sql, array($nom, $numsondage, $nouveauchoix));

			if ($dsondage->mailsonde || /* compatibility for non boolean DB */ $dsondage->mailsonde=="yes" || $dsondage->mailsonde=="true") {
				$headers="From: ".NOMAPPLICATION." <".ADRESSEMAILADMIN.">\r\nContent-Type: text/plain; charset=\"UTF-8\"\nContent-Transfer-Encoding: 8bit";
				mail ("$dsondage->mail_admin",
					"[".NOMAPPLICATION."] "._("Poll's participation")." : $dsondage->titre",
					"\"$nom\" ".
					_("has filled a line.\nYou can find your poll at the link") . " :\n\n".
					getUrlSondage($numsondage)." \n\n" .
					_("Thanks for your confidence.") . "\n". NOMAPPLICATION,
					$headers);
			}
		}
	} else {
		$err |= NAME_EMPTY;
	}
}

// Update vote
$nblignes = $user_studs->RecordCount();
$testmodifier = false;
$ligneamodifier = -1;
for ($i=0;$i<$nblignes;$i++)
{
	if (isset($_POST["modifierligne$i"])) {
		$ligneamodifier = $i;
	}

	//test pour voir si une ligne est a modifier
	if (isset($_POST['validermodifier'.$i])) {
		$modifier = $i;
		$testmodifier = true;
	}
}
if ($testmodifier)
{
	//var_dump($_POST);exit;
	$nouveauchoix = '';
	for ($i=0;$i<$nbcolonnes;$i++)
	{
		//var_dump($_POST["choix$i"]);
		if (isset($_POST["choix$i"]) && $_POST["choix$i"] == '1')
		{
			$nouveauchoix.="1";
		}
		else if (isset($_POST["choix$i"]) && $_POST["choix$i"] == '2')
		{
			$nouveauchoix.="2";
		}
		else { // sinon c'est 0
			$nouveauchoix.="0";
		}
	}

	$compteur=0;
	while ($data = $user_studs->FetchNextObject(false) )
	{
	//mise a jour des données de l'utilisateur dans la base SQL
		if ($compteur == $modifier)
		{
			$sql = 'UPDATE '.MAIN_DB_PREFIX.'opensurvey_user_studs SET reponses='.$connect->Param('nouveauchoix').' WHERE nom='.$connect->Param('nom').' AND id_users='.$connect->Param('id_users');
			$sql = $connect->Prepare($sql);
			$connect->Execute($sql, array($nouveauchoix, $data->nom, $data->id_users));

			if ($dsondage->mailsonde=="yes")
			{
				// TODO Use CMail...
				//$headers="From: ".NOMAPPLICATION." <".ADRESSEMAILADMIN.">\r\nContent-Type: text/plain; charset=\"UTF-8\"\nContent-Transfer-Encoding: 8bit";
				//mail ("$dsondage->mail_admin", "[".NOMAPPLICATION."] " . _("Poll's participation") . " : $dsondage->titre", "\"$data->nom\""."" . _("has filled a line.\nYou can find your poll at the link") . " :\n\n".getUrlSondage($numsondage)." \n\n" . _("Thanks for your confidence.") . "\n".NOMAPPLICATION,$headers);
			}
		}

		$compteur++;
	}
}



/*
 * View
 */

$form=new Form($db);
$object=new OpenSurveySondage($db);

$arrayofjs=array('/opensurvey/block_enter.js');
$arrayofcss=array('/opensurvey/css/style.css');
llxHeaderSurvey($dsondage->titre, "", 0, 0, $arrayofjs, $arrayofcss);

$object->fetch(0,$numsondage);

if($err != 0) {
	echo '<div class="error"><ul>'."\n";
	if(is_error(NAME_EMPTY)) {
		echo '<li class="error">' . _("Enter a name !") . "</li>\n";
	}
	if(is_error(NAME_TAKEN)) {
		echo '<li class="error">' .
			_("The name you've chosen already exist in this poll!") .
			"</li>\n";
	}
	if(is_error(COMMENT_EMPTY) || is_error(COMMENT_USER_EMPTY)) {
		echo '<li class="error">' .
			_("Enter a name and a comment!") .
			"</li>\n";
	}
	if(is_error(COMMENT_INSERT_FAILED) ) {
		echo '<li class="error">' .
			_("Failed to insert the comment!") .
			"</li>\n";
	}
	echo '</ul></div>';

	if(is_error(NO_POLL_ID) || is_error(NO_POLL)) {
		echo '<div class="corpscentre">'."\n";
		print "<H2>" . _("This poll doesn't exist !") . "</H2>"."\n";
		print _("Back to the homepage of") . ' <a href="index.php"> '. NOMAPPLICATION . '</a>.'."\n";
		echo '<br><br><br><br>'."\n";
		echo '</div>'."\n";

		echo '</body>'."\n";
		echo '</html>'."\n";
		die();
	}
}

echo '<div class="corps"> '."\n";

//affichage du titre du sondage
$titre=str_replace("\\","",$dsondage->titre);
echo '<strong>'.$titre.'</strong><br>'."\n";

//affichage du nom de l'auteur du sondage
echo $langs->trans("InitiatorOfPoll") .' : '.$dsondage->nom_admin.'<br>'."\n";

//affichage des commentaires du sondage
if ($dsondage->commentaires) {
	echo '<br>'.$langs->trans("Description") .' :<br>'."\n";
	$commentaires=dol_nl2br($dsondage->commentaires);
	echo $commentaires;
	echo '<br>'."\n";
}

echo '</div>'."\n";

echo '<form name="formulaire" action="studs.php?sondage='.$numsondage.'"'.'#bas" method="POST" onkeypress="javascript:process_keypress(event)">'."\n";
echo '<input type="hidden" name="sondage" value="' . $numsondage . '"/>';
// Todo : add CSRF protection
echo '<div class="cadre"> '."\n";
echo $langs->trans("OpenSurveyHowTo");
echo '<br><br>'."\n";

// Debut de l'affichage des resultats du sondage
echo '<table class="resultats">'."\n";

//recuperation des utilisateurs du sondage
$sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'opensurvey_user_studs WHERE id_sondage='.$connect->Param('numsondage').' ORDER BY id_users';
$sql = $connect->Prepare($sql);
$user_studs = $connect->Execute($sql, array($numsondage));

//reformatage des données des sujets du sondage
$toutsujet = explode(",",$object->sujet);
$listofanswers=array();
foreach ($toutsujet as $value)
{
	$tmp=explode('@',$value);
	$listofanswers[]=array('label'=>$tmp[0],'format'=>$tmp[1]);
}

//si le sondage est un sondage de date
if ($dsondage->format=="D"||$dsondage->format=="D+")
{
	//affichage des sujets du sondage
	echo '<tr>'."\n";
	echo '<td></td>'."\n";

	//affichage des années
	$colspan=1;
	for ($i=0;$i<count($toutsujet);$i++) {
		if (isset($toutsujet[$i+1]) && date('Y', intval($toutsujet[$i])) == date('Y', intval($toutsujet[$i+1]))) {
			$colspan++;
		} else {
			echo '<td colspan='.$colspan.' class="annee">'.date('Y', intval($toutsujet[$i])).'</td>'."\n";
			$colspan=1;
		}
	}

	echo '</tr>'."\n";
	echo '<tr>'."\n";
	echo '<td></td>'."\n";

	//affichage des mois
	$colspan=1;
	for ($i=0;$i<count($toutsujet);$i++) {
		// intval() est utiliser pour supprimer le suffixe @* qui déplaît logiquement à strftime()
		$cur = intval($toutsujet[$i]);
		if (isset($toutsujet[$i+1]) === false) {
			$next = false;
		} else {
			$next = intval($toutsujet[$i+1]);
		}

		if ($next && strftime("%B", $cur) == strftime("%B", $next) && date('Y', $cur) == date('Y', $next)) {
			$colspan++;
		} else {
			if ($_SESSION["langue"]=="EN") { // because strftime doesn't support english suffix (like st,nd,rd,th)
				echo '<td colspan='.$colspan.' class="mois">'.date("F",$cur).'</td>'."\n";
			} else {
				echo '<td colspan='.$colspan.' class="mois">'.strftime("%B",$cur).'</td>'."\n";
			}
			$colspan=1;
		}
	}

	echo '</tr>'."\n";
	echo '<tr>'."\n";
	echo '<td></td>'."\n";

	//affichage des jours
	$colspan=1;
	for ($i=0;$i<count($toutsujet);$i++) {
		$cur = intval($toutsujet[$i]);
		if (isset($toutsujet[$i+1]) === false) {
			$next = false;
		} else {
			$next = intval($toutsujet[$i+1]);
		}
		if ($next && strftime("%a %e", $cur) == strftime("%a %e", $next) && strftime("%B", $cur) == strftime("%B", $next)) {
			$colspan++;
		} else {
			if ($_SESSION["langue"]=="EN") {
				echo '<td colspan='.$colspan.' class="jour">'.date("D jS",$cur).'</td>'."\n";
			} else {
				echo '<td colspan='.$colspan.' class="jour">'.strftime("%a %e",$cur).'</td>'."\n";
			}

			$colspan=1;
		}
	}

	echo '</tr>'."\n";

	//affichage des horaires
	if (strpos($dsondage->sujet, '@') !== false) {
		echo '<tr>'."\n";
		echo '<td></td>'."\n";

		for ($i=0; isset($toutsujet[$i]); $i++) {
			$heures=explode("@",$toutsujet[$i]);
			if (isset($heures[1]) === true) {
				echo '<td class="heure">'.$heures[1].'</td>'."\n";
			} else {
				echo '<td class="heure"></td>'."\n";
			}
		}

		echo '</tr>'."\n";
	}
}
else
{
	$toutsujet=str_replace("°","'",$toutsujet);

	//affichage des sujets du sondage
	echo '<tr>'."\n";
	echo '<td></td>'."\n";

	for ($i=0; isset($toutsujet[$i]); $i++)
	{
		$tmp=explode('@',$toutsujet[$i]);
		echo '<td class="sujet">'.$tmp[0].'</td>'."\n";
	}

	echo '</tr>'."\n";
}

//Usager pré-authentifié dans la liste?
$user_mod = false;


// Loop on each answers
$somme = array();
$compteur = 0;
while ($data = $user_studs->FetchNextObject(false))
{
	echo '<tr>'."\n";
	echo '<td class="nom">';

	// Le nom de l'utilisateur
	$nombase=str_replace("°","'",$data->nom);
	echo $nombase.'</td>'."\n";

	// Les réponses qu'il a choisit
	$ensemblereponses = $data->reponses;

	// ligne d'un usager pré-authentifié
	$mod_ok = !isset($_SERVER['REMOTE_USER']) || ($nombase == $_SESSION['nom']);
	$user_mod |= $mod_ok;

	// pour chaque colonne
	for ($i=0; $i < $nbcolonnes; $i++)
	{
		$car = substr($ensemblereponses, $i, 1);
		if ($compteur == $ligneamodifier)
		{
			echo '<td class="vide">';
			if (empty($listofanswers[$i]['format']) || $listofanswers[$i]['format'] == 'yesno')
			{
				print '<input type="checkbox" name="choix'.$i.'" value="1" ';
				if ($car == '1') echo 'checked="checked"';
				echo '>';
			}
			if (! empty($listofanswers[$i]['format']) && $listofanswers[$i]['format'] == 'pourcontre')
			{
				$arraychoice=array('2'=>'&nbsp;','0'=>$langs->trans("Against"),'1'=>$langs->trans("For"));
				print $form->selectarray("choix".$i, $arraychoice, $car);
			}
			print '</td>'."\n";
		}
		else
		{
			if (empty($listofanswers[$i]['format']) || $listofanswers[$i]['format'] == 'yesno')
			{
				if ($car == "1") echo '<td class="ok">OK</td>'."\n";
				else echo '<td class="non">&nbsp;</td>'."\n";
				// Total
				if (isset($somme[$i]) === false) $somme[$i] = 0;
				if ($car == "1") $somme[$i]++;
			}
			if (! empty($listofanswers[$i]['format']) && $listofanswers[$i]['format'] == 'pourcontre')
			{
				if ($car == "1") echo '<td class="ok">'.$langs->trans("For").'</td>'."\n";
				else if ($car =="0") echo '<td class="non">'.$langs->trans("Against").'</td>'."\n";
				else echo '<td class="vide">&nbsp;</td>'."\n";
				// Total
				if (isset($somme[$i]) === false) $somme[$i] = 0;
				if ($car == "1") $somme[$i]++;
			}
		}
	}

	//a la fin de chaque ligne se trouve les boutons modifier
	if ($compteur != $ligneamodifier && ($dsondage->format=="A+"||$dsondage->format=="D+") && $mod_ok) {
		echo '<td class=casevide><input type="submit" class="button" name="modifierligne'.$compteur.'" value="'.dol_escape_htmltag($langs->trans("Edit")).'"></td>'."\n";
	}

	//demande de confirmation pour modification de ligne
	for ($i=0;$i<$nblignes;$i++) {
		if (isset($_POST["modifierligne$i"]) || isset($_POST['modifierligne'.$i.'_x'])) {
			if ($compteur == $i) {
				echo '<td class="casevide"><input type="submit" class="button" name="validermodifier'.$compteur.'" value="'.dol_escape_htmltag($langs->trans("Save")).'"></td>'."\n";
			}
		}
	}

	$compteur++;
	echo '</tr>'."\n";
}

// Add line to add new record
if ($ligneamodifier < 0 && (!isset($_SERVER['REMOTE_USER']) || ! $user_mod))
{
	echo '<tr>'."\n";
	echo '<td class="nom">'."\n";
	if (isset($_SESSION['nom'])) {
		echo '<input type=hidden name="nom" value="'.$_SESSION['nom'].'">'.$_SESSION['nom']."\n";
	} else {
		echo '<input type=text name="nom" maxlength="64">'."\n";
	}
	echo '</td>'."\n";

	// affichage des cases de formulaire checkbox pour un nouveau choix
	for ($i=0;$i<$nbcolonnes;$i++)
	{
		echo '<td class="vide">';
		if (empty($listofanswers[$i]['format']) || $listofanswers[$i]['format'] == 'yesno')
		{
			print '<input type="checkbox" name="choix'.$i.'" value="1"';
			if ( isset($_POST['choix'.$i]) && $_POST['choix'.$i] == '1' && is_error(NAME_EMPTY) )
			{
				echo ' checked="checked"';
			}
			echo '>';
		}
		if (! empty($listofanswers[$i]['format']) && $listofanswers[$i]['format'] == 'pourcontre')
		{
			$arraychoice=array('2'=>'&nbsp;','0'=>$langs->trans("Against"),'1'=>$langs->trans("For"));
			print $form->selectarray("choix".$i, $arraychoice);
		}
		print '</td>'."\n";
	}

	// Affichage du bouton de formulaire pour inscrire un nouvel utilisateur dans la base
	echo '<td><input type="image" name="boutonp" value="' . _('Participate') . '" src="images/add-24.png"></td>'."\n";
	echo '</tr>'."\n";
}

//determination de la meilleure date
// On cherche la meilleure colonne
for ($i=0; $i < $nbcolonnes; $i++) {
	if (isset($somme[$i]) === true) {
		if ($i == "0") {
			$meilleurecolonne = $somme[$i];
		}

		if (isset($meilleurecolonne) === false || $somme[$i] > $meilleurecolonne) {
			$meilleurecolonne = $somme[$i];
		}
	}
}

// Affichage des différentes sommes des colonnes existantes
echo '<tr>'."\n";
echo '<td align="right">'. $langs->trans("Total") .'</td>'."\n";

for ($i=0; $i < $nbcolonnes; $i++) {
	if (isset($somme[$i]) === true) {
		$affichesomme = $somme[$i];

		if ($affichesomme == "") {
			$affichesomme = '0';
		}
	} else {
		$affichesomme = '0';
	}

	echo '<td class="somme">'.$affichesomme.'</td>'."\n";
}

echo '</tr>'."\n";
echo '<tr>'."\n";
echo '<td class="somme"></td>'."\n";

for ($i=0; $i < $nbcolonnes; $i++) {
	if (isset($somme[$i]) && isset($meilleurecolonne) && $somme[$i] == $meilleurecolonne) {
		echo '<td class="somme"><img src="images/medaille.png" alt="' . _('Best choice') . '"></td>'."\n";
	} else {
		echo '<td class="somme"></td>'."\n";
	}
}

echo '</tr>'."\n";
echo '</table>'."\n";
echo '</div>'."\n";

// reformatage des données de la base pour les sujets
$toutsujet=explode(",",$dsondage->sujet);
$toutsujet=str_replace("°","'",$toutsujet);

// On compare le nombre de résultat avec le meilleur et si le résultat est égal
//  on concatene le resultat dans $meilleursujet
$compteursujet=0;
$meilleursujet = '';

for ($i = 0; $i < $nbcolonnes; $i++) {
	if (isset($somme[$i]) && isset($meilleurecolonne) && $somme[$i] == $meilleurecolonne) {
		$meilleursujet.=", ";
		if ($dsondage->format=="D"||$dsondage->format=="D+") {
			$meilleursujetexport = $toutsujet[$i];

			if (strpos($toutsujet[$i], '@') !== false) {
				$toutsujetdate = explode("@", $toutsujet[$i]);
				$meilleursujet .= dol_print_date($toutsujetdate[0],'daytext'). ' ('.dol_print_date($toutsujetdate[0],'%A').')' . _("for")  . ' ' . $toutsujetdate[1];
			} else {
				$meilleursujet .= dol_print_date($toutsujet[$i],'daytext'). ' ('.dol_print_date($toutsujet[$i],'%A').')';
			}
		} else {
			$meilleursujet .= $toutsujet[$i];
		}

		$compteursujet++;
	}
}

$meilleursujet=substr("$meilleursujet", 1);
$vote_str = _('vote');

if (isset($meilleurecolonne) && $meilleurecolonne > 1) {
	$vote_str = _('votes');
}

echo '<p class="affichageresultats">'."\n";

// Affichage du meilleur choix
if ($compteursujet == "1" && isset($meilleurecolonne)) {
	print '<img src="images/medaille.png" alt="Meilleur choix"> ' . $langs->trans('TheBestChoice') . ": <b>$meilleursujet</b> " . $langs->trans('with') . " <b>$meilleurecolonne </b>" . $vote_str . ".\n";
} elseif (isset($meilleurecolonne)) {
	print '<img src="images/medaille.png" alt="Meilleur choix"> ' . $langs->trans('TheBestChoices')  . ": <b>$meilleursujet</b> " . $langs->trans('with') . "  <b>$meilleurecolonne </b>" . $vote_str . ".\n";
}

echo '</p>';

//affichage des commentaires des utilisateurs existants
$sql = 'select * from '.MAIN_DB_PREFIX.'opensurvey_comments where id_sondage='.$connect->Param('numsondage').' order by id_comment';
$sql = $connect->Prepare($sql);
$comment_user=$connect->Execute($sql, array($numsondage));

if ($comment_user->RecordCount() != 0) {
	print "<br><b>" . $langs->trans("CommentsOfVoters") . " :</b><br>\n";
	while($dcomment = $comment_user->FetchNextObject(false)) {
		print '<div class="comment"><span class="usercomment">'.$dcomment->usercomment. ' :</span> <span class="comment">' . nl2br($dcomment->comment) . '</span></div>';
	}
}

//affichage de la case permettant de rajouter un commentaire par les utilisateurs
print '<div class="addcomment">' .$langs->trans("AddACommentForPoll") . "<br>\n";

echo '<textarea name="comment" rows="2" cols="60"></textarea><br>'."\n";
if (isset($_SESSION['nom']) === false)
{
	echo $langs->trans("Name") .' : ';
	echo '<input type="text" name="commentuser" maxlength="64" /> &nbsp; '."\n";
}
echo '<input type="submit" class="button" name="ajoutcomment" value="'.dol_escape_htmltag($langs->trans("AddComment")).'"><br>'."\n";
echo '</form>'."\n";
// Focus javascript sur la case de texte du formulaire
echo '</div>'."\n";

echo '<br><br>';


// Define $urlwithroot
$urlwithouturlroot=preg_replace('/'.preg_quote(DOL_URL_ROOT,'/').'$/i','',trim($dolibarr_main_url_root));
$urlwithroot=$urlwithouturlroot.DOL_URL_ROOT;		// This is to use external domain name found into config file
//$urlwithroot=DOL_MAIN_URL_ROOT;					// This is to use same domain name than current

$message='';
$url=$urlwithouturlroot.dol_buildpath('/opensurvey/public/studs.php',1).'?sondage='.$numsondage;
$urlvcal='<a href="'.$url.'" target="_blank">'.$url.'</a>';
$message.=img_picto('','object_globe.png').' '.$langs->trans("UrlForSurvey",$urlvcal);

print '<center>'.$message.'</center>';



echo '<a name="bas"></a>'."\n";

llxFooterSurvey();