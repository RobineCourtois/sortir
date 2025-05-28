<?php

namespace App\Utils;

enum Etat: string
{
	case EN_CREATION = 'En création';
	case OUVERTE = 'Ouverte';
	case CLOTUREE = 'Clôturée';
	case EN_COURS = 'En cours';
	case TERMINEE = 'Terminée';
	case ANNULEE = 'Anuulée';
	case HISTORISEE = 'Historisée';

}
