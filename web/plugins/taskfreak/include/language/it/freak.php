<?php
/****************************************************************************\
* TaskFreak!                                                                 *
* multi user                                                                 *
******************************************************************************
* Version: 0.5.7                                                             *
* Authors: Stan Ozier <taskfreak@gmail.com>                                  *
* License:  http://www.gnu.org/licenses/gpl.txt (GPL)                        *
\****************************************************************************/

$GLOBALS['langParams'] = array (
	'jscalendar'	=> 'it'
);

// top menu / navigation
$GLOBALS['langMenu'] = array (
	'task'				=> 'Task',
    'print_list'        => 'Versione stampabile',
	'new_todo'			=> 'Nuovo TODO',
	'view'				=> 'Vedi',
	'all_projects'		=> 'Tutti i Progetti',
	'future_tasks'		=> 'Task Futuri',
	'past_tasks'		=> 'Task Passati',
    'my_tasks'          => 'I miei Task',
	'all_tasks'			=> 'Tutti i Task',
	'all_contexts'		=> 'Tutti i Contesti',
	'all_users' 		=> 'Tutti gli Utenti',
	'reload'			=> 'Ricarica',
	'manage'			=> 'Gestisci',
	'projects'			=> 'Progetti',
	'users' 			=> 'Utenti',
    'preferences'       => 'Il Mio Profilo',
    'settings'          => 'System Settings',
	'login'				=> 'Login',
	'logout'			=> 'Logout',
	'warning'			=> 'Warning',
	'warning_install'	=> 'Install folder still exists, you should delete it for security purposes'
);

// fields and column labels
$GLOBALS['langForm'] = array (
	'priority'			=> 'Priorita\'',
	'context'			=> 'Contesto',
	'deadline'			=> 'DataTermine',
	'project'			=> 'Progetto',
	'tasks'				=> 'Tasks',
	'title'				=> 'Titolo',
	'description'		=> 'Descrizione',
	'user'              => 'Utente',
	'visibility'		=> 'Visibility', //-TODO-
    'private'           => 'privato',
    'internal'          => 'interno',
    'public'            => 'pubblico',
	'status'			=> 'Status',
	'create'			=> 'Generare',
	'save'				=> 'Salva',
	'cancel'			=> 'Annulla',
	'reset'				=> 'Reset form',
	'close'             => 'chuidi',
    'edit'              => 'midifica',
    'delete'            => 'elimina',
	'new'				=> 'Nuovo',
	'project_new'		=> 'Nuovo Progetto?',
	'project_list'		=> 'Mostra la lista',
	'compulsory_legend' => 'I campi in <span class="compulsory">rosso</span> sono obbligatori.',
	'list_comments'		=> 'Com.'
);

$GLOBALS['langTaskDetails'] = array (
	'tab_description'	=> 'description',
	'description_none'	=> 'no description',
	'tab_comments'		=> 'comments',
	'comments_by'		=> 'by',
	'comments_none'		=> 'no comment left yet',
	'comments_no_access'	=> 'comments are confidential',
	'comments_new'		=> 'post first comment',
	'comments_reply'	=> 'reply',
	'comments_edit'		=> 'edit',
	'comments_delete'	=> 'delete',
	'comments_delete_confirm'	=> 'really delete comment?',
	'tab_history'		=> 'history',
    'history_date'      => 'data',
    'history_user'      => 'utente',
    'history_what'      => 'azione'
);

// date support
$GLOBALS['langDateMore'] = array (
	'day'				=> 'giorno',
	'days'				=> 'giorni',
	'help'				=> 'ad es. oggi, domani, 12 apr'
);

// project related
$GLOBALS['langProject'] = array(
    'project'           => 'Progetto',
    'projects'          => 'Progetti',
    'name'              => 'Nome',
    'description'       => 'Descrizione',
    'position'          => 'Posizione',
    'members'           => 'Membri',
    'members_legend'    => 'Membi del Progetto',
    'status'            => 'Status',
    'action'            => 'Azione',
    'project_history'   => 'Visualizza la cronologia degli status',
    'remove_confirm'    => 'Confermi la rimozione dal progetto?',
    'user_add_legend'   => 'Aggiungi un utente a questo progetto',
    'user_add_button'   => 'Aggiungi un utente al progetto',
	'user_no_project'   => 'Non e\' assegnato a nessun progetto',
	'user_added_ok'		=> 'Member successfully added to project',
	'user_added_err'	=> 'Member already belongs to project or is not available',
	'user_removed_ok'	=> 'Member removed from project!',
	'user_removed_err'	=> 'Member can not be removed or has already been removed',
	'user_position_ok'	=> 'Member position(s) successfully updated',
	'project_info'		=> 'Progetto info',
	'history_date'      => 'data',
    'history_user'      => 'utente',
	'history_what'      => 'azione',
	'action_save_ok'	=> 'Project details updated!',
	'action_added_ok'	=> 'Project created!',
	'action_status_ok'	=> 'Project status updated!'
);

// project related
$GLOBALS['langUser'] = array(
    'information'       => 'Informazioni Personali',
    'user'              => 'Utente',
    'name'              => 'Nome',
    'title'             => 'Titolo',
    'first_name'        => 'Nome',
    'middle_name'       => 'Secondo nome',
    'last_name'         => 'Cognome',
    'address'           => 'Indirizzo',
    'location'          => 'Ubicazione',
    'city'              => 'Citta\'',
    'state'             => 'Provincia',
    'country'           => 'Nazione',
    'email'             => 'Email',
    'position'          => 'Livello',
	'last_login'        => 'Ultimo accesso',
	'last_login_from'	=> 'From',
	'logout_goodbye'	=> 'You are now logged out. Goodbye.',
	'logout_login'		=> 'Click here to login',
    'action'            => 'Azione',
    'delete_confirm'    => 'Confermi la cancellazione di questo utente?',
    'enable_confirm'    => 'Confermi l\'abilitazione di questo utente?',
    'disable_confirm'   => 'Confermi la disabilitazione di questo utente?',
    'account'           => 'Credenziali di accesso',
    'account_legend'    => 'Scegli un username ed una password per accedere al Sistema!',
    'username'          => 'Username',
    'password'          => 'Password',
    'password_confirm'  => '(conferma)',
    'auto_login'        => 'Ricorda i miei dati di accesso su questo computer',
    'password_legend'   => 'Inserisci una password (e confermala) solo se vuoi cambiarla.',
    'enabled_label'     => 'Account attivo',
	'login_signup'		=> 'Not a member? Request an account here',
	'account_created'	=> 'Created on',
	'account_disabled'	=> 'Account is disabled!',
	'state_us_only'		=> 'for US members only'
);

// buttons
$GLOBALS['langButton'] = array(
    'add'               => 'Crea',
	'add_account'		=> 'Submit',
    'update'            => 'Salva le modifiche',
    'cancel'            => 'Anulla le modifiche',
    'reset'             => 'Resetta il form',
    'back'              => 'Torna alla lista'
);


// error and information messages
$GLOBALS['langMessage'] = array (
    'not_found_or_denied'       => 'Dato non trovato o Accesso negato',
    'denied'                    => 'Accesso negato!',
    'project_delete'            => 'Cancella il progetto',
    'project_delete_confirm'    => 'Confermi la cancellazione del progetto e di tutti i suoi tasks?',
    'project_delete_ok'         => 'Progetto cancellato',
    'project_delete_no'         => 'Questo progetto non può essere cancellato!',
    'task_edit'				    => 'Midifica questo task',
    'task_delete'			    => 'Cancella questo task',
    'task_delete_confirm'	    => 'Confermi la cancellazione di questo task?',
	'error_no_title'		    => 'Inserisci un titolo!',
	'done_deleted'			    => 'task cancellato!',
	'done_status'			    => 'status del task aggiornato!',
	'done_updated'			    => 'task aggiornato!',
	'done_added'			    => 'task creato!',
	'done_comment_added'		=> 'comment creato!',
	'done_comment_updated'		=> 'comment aggiornato!',
	'done_comment_deleted'		=> 'comment cancellato!',
	'operation_failed'			=> 'operation failed!',
	'purge_all'				    => 'Elimina i vecchi task per tutti i progetti',
	'purge_all_confirm'		    => 'Confermi l\'eliminazione dei vecchi task per tutti i progetti?',
	'delete_all'			    => 'Elimina tutti i progetti (e tutti i loro tasks)',
	'delete_all_confirm'	    => 'Confermi l\'eliminazione di tutti i task e di tutti i progetti?',
	'purge_one'				    => 'Elimina (cancella i vecchi task)',
	'purge_one_confirm'		    => 'Confermi l\'eliminazione dei vecchi task di questo progetto?',
	'delete_one'			    => 'Elimina tutto il progetto',
	'delete_one_confirm'	    => 'Confermi l\'eliminazione di questo progetto?',
	'no_task_found'			    => 'Nessun task corrisponde ai criteri selezionati',
	'no_project_found'		    => 'Non ci sono progetti attivi',
	'create_task'			    => 'Clicca qui per creare un nuovo Task',
	'no_project_found_1'	    => "Ahia! Non riesco a trovare un progetto!!!",
	'no_project_found_2'	    => 'Probabilmente devi creare prima un Task, lo sai?',
	'close_window'			    => 'chiudi questa finestra',
    'session_expired'           => 'La sessione e\' scaduta!',
	'information_saved'			=> 'Information successfully saved',
	'clock_start'				=> 'start timer',
    'clock_stop'				=> 'stop timer',
    'clock_change'				=> 'modify timer',
	'confirm_status_close'		=> 'Really close this task?'
);

$GLOBALS['langRss'] = array (
    'no_task'       => 'Nessun Task per oggi',
    'error_login'   => 'Autenticazione fallita'
);
