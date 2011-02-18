<?php
/* Copyright (C) 2005      Patrick Rouillon     <patrick@rouillon.net>
 * Copyright (C) 2005-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *       \file       htdocs/cabinetmed/contact.php
 *       \ingroup    cabinetmed
 *       \brief      Tab for links between doctors and patient
 *       \version    $Id: contact.php,v 1.3 2011/02/13 12:16:30 eldy Exp $
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../dolibarr/htdocs/main.inc.php");     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (! $res && file_exists("../../../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (! $res) die("Include of main fails");
include_once(DOL_DOCUMENT_ROOT."/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
include_once("./class/patient.class.php");
include_once("./class/cabinetmedcons.class.php");

$langs->load("cabinetmed@cabinetmed");
$langs->load("orders");
$langs->load("sendings");
$langs->load("companies");

// Security check
$socid = GETPOST("socid");
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'societe', $socid);


/*
 * Add new contact
 */

if ($_POST["action"] == 'addcontact' && $user->rights->societe->creer)
{
	if ($_POST["contactid"] && $_POST["type"])
	{
		$result = 0;
		$societe = new Societe($db);
		$result = $societe->fetch($socid);
	
	    if ($result > 0 && $socid > 0)
	    {
	  		$result = $societe->add_contact($_POST["contactid"], $_POST["type"], $_POST["source"]);
	    }
	
		if ($result >= 0)
		{
			Header("Location: contact.php?socid=".$societe->id);
			exit;
		}
		else
		{
			if ($societe->error == 'DB_ERROR_RECORD_ALREADY_EXISTS')
			{
				$langs->load("errors");
				$mesg = '<div class="error">'.$langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType").'</div>';
			}
			else
			{
				$mesg = '<div class="error">'.$societe->error.'</div>';
			}
		}
	}
}
// modification d'un contact. On enregistre le type
if ($_POST["action"] == 'updateligne' && $user->rights->commande->creer)
{
	$societe = new Societe($db);
	if ($societe->fetch($socid))
	{
		$contact = $societe->detail_contact($_POST["elrowid"]);
		$type = $_POST["type"];
		$statut = $contact->statut;

		$result = $societe->update_contact($_POST["elrowid"], $statut, $type);
		if ($result >= 0)
		{
			$db->commit();
		} else
		{
			dol_print_error($db, "result=$result");
			$db->rollback();
		}
	} else
	{
		dol_print_error($db);
	}
}

// bascule du statut d'un contact
if ($_GET["action"] == 'swapstatut' && $user->rights->societe->creer)
{
	$societe = new Societe($db);
	if ($societe->fetch($socid))
	{
		$contact = $societe->detail_contact($_GET["ligne"]);
		$id_type_contact = $contact->fk_c_type_contact;
		$statut = ($contact->statut == 4) ? 5 : 4;

		$result = $societe->update_contact($_GET["ligne"], $statut, $id_type_contact);
		if ($result >= 0)
		{
			$db->commit();
		} else
		{
			dol_print_error($db, "result=$result");
			$db->rollback();
		}
	} else
	{
		dol_print_error($db);
	}
}

// Efface un contact
if ($_GET["action"] == 'deleteline' && $user->rights->societe->creer)
{
	$societe = new Societe($db);
	$societe->fetch($socid);
	$result = $societe->delete_contact($_GET["lineid"]);

	if ($result >= 0)
	{
		Header("Location: contact.php?socid=".$societe->id);
		exit;
	}
	else {
		dol_print_error($db);
	}
}


/*
 * View
 */

llxHeader('',$langs->trans('Contacts'),'');

$html = new Form($db);
$form = new Form($db);
$formcompany = new FormCompany($db);
$contactstatic=new Contact($db);
$userstatic=new User($db);


/* *************************************************************************** */
/*                                                                             */
/* Mode vue et edition                                                         */
/*                                                                             */
/* *************************************************************************** */
if (isset($mesg)) print $mesg;

$id = $_GET['socid'];
$ref= $_GET['ref'];
if ($id > 0 || ! empty($ref))
{
	$societe = new Societe($db);
	$societe->fetch($id);


	$head = societe_prepare_head($societe);
    dol_fiche_head($head, 'tabcontacts', $langs->trans("ThirdParty"),0,'company');

    print '
                <script>
            jQuery(function() {
                jQuery( "#contactid" ).combobox();
            });
            </script>
    ';

    print "<form method=\"post\" action=\"".$_SERVER["PHP_SELF"]."\">";
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';

    print '<table class="border" width="100%">';

    print '<tr><td width="25%">'.$langs->trans('Name').'</td>';
    print '<td colspan="3">';
    print $form->showrefnav($societe,'socid','',($user->societe_id?0:1),'rowid','nom');
    print '</td></tr>';

    if ($societe->client)
    {
        print '<tr><td>';
        print $langs->trans('CustomerCode').'</td><td colspan="3">';
        print $societe->code_client;
        if ($societe->check_codeclient() <> 0) print ' <font class="error">('.$langs->trans("WrongCustomerCode").')</font>';
        print '</td></tr>';
    }

    if ($societe->fournisseur)
    {
        print '<tr><td>';
        print $langs->trans('SupplierCode').'</td><td colspan="3">';
        print $societe->code_fournisseur;
        if ($societe->check_codefournisseur() <> 0) print ' <font class="error">('.$langs->trans("WrongSupplierCode").')</font>';
        print '</td></tr>';
    }

    print "</table>";

    print '</form>';

    dol_fiche_end();

	/*
	* Lignes de contacts
	*/
	echo '<br><table class="noborder" width="100%">';

	/*
	* Ajouter une ligne de contact
	* Non affiche en mode modification de ligne
	*/
	if ($_GET["action"] != 'editline')
	{
		print '<tr class="liste_titre">';
		//print '<td>'.$langs->trans("Source").'</td>';
		print '<td>'.$langs->trans("Contacts").'</td>';
		print '<td>'.$langs->trans("ContactType").'</td>';
		print '<td colspan="3">&nbsp;</td>';
		print "</tr>\n";

		$var = true;

        /*
		print '<form action="contact.php?socid='.$id.'" method="post">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="addcontact">';
		print '<input type="hidden" name="source" value="internal">';
		print '<input type="hidden" name="socid" value="'.$id.'">';

		// Ligne ajout pour contact interne
		print "<tr $bc[$var]>";

		print '<td nowrap="nowrap">';
		print img_object('','user').' '.$langs->trans("Users");
		print '</td>';

		print '<td colspan="1">';
		print $conf->global->MAIN_INFO_SOCIETE_NOM;
		print '</td>';

		print '<td colspan="1">';
		//$userAlreadySelected = $commande->getListContactId('internal');	// On ne doit pas desactiver un contact deja selectionner car on doit pouvoir le seclectionner une deuxieme fois pour un autre type
		$html->select_users($user->id,'contactid',0,$userAlreadySelected);
		print '</td>';
		print '<td>';
		$formcompany->selectTypeContact($commande, '', 'type','internal');
		print '</td>';
		print '<td align="right" colspan="3" ><input type="submit" class="button" value="'.$langs->trans("Add").'"></td>';
		print '</tr>';

		print '</form>';
        */

		print '<form action="contact.php?socid='.$socid.'" method="post">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="addcontact">';
		print '<input type="hidden" name="source" value="external">';
		print '<input type="hidden" name="socid" value="'.$socid.'">';

		// Ligne ajout pour contact externe
		$var=!$var;
		print "<tr $bc[$var]>";

		/*print '<td nowrap="nowrap">';
		print img_object('','contact').' '.$langs->trans("ThirdPartyContacts");
		print '</td>';
		*/
		
		print '<td colspan="1">';
		// $contactAlreadySelected = $commande->getListContactId('external');	// On ne doit pas desactiver un contact deja selectionner car on doit pouvoir le seclectionner une deuxieme fois pour un autre type
		$nbofcontacts=$html->select_contacts(0, '', 'contactid', 1);
		if ($nbofcontacts == 0) print $langs->trans("NoContactDefined");
		print '</td>';
		print '<td>';
		$formcompany->selectTypeContact($societe, '', 'type','external','libelle',1);
		print '</td>';
		print '<td align="right" colspan="3" ><input type="submit" class="button" value="'.$langs->trans("Add").'"';
		if (! $nbofcontacts) print ' disabled="true"';
		print '></td>';
		print '</tr>';

		print "</form>";

		print '<tr><td colspan="6">&nbsp;</td></tr>';
	}

	// List of linked contacts
	print '<tr class="liste_titre">';
	//print '<td>'.$langs->trans("Source").'</td>';
	print '<td>'.$langs->trans("Contacts").'</td>';
	print '<td>'.$langs->trans("ContactType").'</td>';
	print '<td align="center">'.$langs->trans("Status").'</td>';
	print '<td colspan="2">&nbsp;</td>';
	print "</tr>\n";

	$companystatic=new Societe($db);
	$var = true;

	foreach(array('external') as $source)
	{
		$tab = $societe->liste_contact(-1,$source);
		$num=sizeof($tab);

		$i = 0;
		while ($i < $num)
		{
			$var = !$var;

			print '<tr '.$bc[$var].' valign="top">';

			// Source
			/*print '<td align="left">';
			if ($tab[$i]['source']=='internal') print $langs->trans("User");
			if ($tab[$i]['source']=='external') print $langs->trans("ThirdPartyContact");
			print '</td>';
			*/
			
			// Societe
			/*print '<td align="left">';
			if ($tab[$i]['socid'] > 0)
			{
				$companystatic->fetch($tab[$i]['socid']);
				print $companystatic->getNomUrl(1);
			}
			if ($tab[$i]['socid'] < 0)
			{
				print $conf->global->MAIN_INFO_SOCIETE_NOM;
			}
			if (! $tab[$i]['socid'])
			{
				print '&nbsp;';
			}
			print '</td>';
			*/
			
			// Contact
			print '<td>';
            if ($tab[$i]['source']=='internal')
            {
                $userstatic->id=$tab[$i]['id'];
                $userstatic->nom=$tab[$i]['nom'];
                $userstatic->prenom=$tab[$i]['firstname'];
                print $userstatic->getNomUrl(1);
            }
            if ($tab[$i]['source']=='external')
            {
                $contactstatic->id=$tab[$i]['id'];
                $contactstatic->name=$tab[$i]['nom'];
                $contactstatic->firstname=$tab[$i]['firstname'];
                print $contactstatic->getNomUrl(1);
            }
			print '</td>';

			// Type de contact
			print '<td>'.$tab[$i]['libelle'].'</td>';

			// Statut
			print '<td align="center">';
			// Activation desativation du contact
			if ($societe->statut >= 0)	print '<a href="contact.php?socid='.$societe->id.'&amp;action=swapstatut&amp;ligne='.$tab[$i]['rowid'].'">';
			print $contactstatic->LibStatut($tab[$i]['status'],3);
			if ($societe->statut >= 0)	print '</a>';
			print '</td>';

			// Icon update et delete
			print '<td align="center" nowrap>';
			if ($societe->statut < 5 && $user->rights->societe->creer)
			{
				print '&nbsp;';
				print '<a href="contact.php?socid='.$societe->id.'&amp;action=deleteline&amp;lineid='.$tab[$i]['rowid'].'">';
				print img_delete();
				print '</a>';
			}
			print '</td>';

			print "</tr>\n";

			$i ++;
		}
	}
	print "</table>";
}

$db->close();

llxFooter('$Date: 2011/02/13 12:16:30 $');
?>