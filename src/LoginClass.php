<?php

/**
*
* @author  Antonio de la Rosa <webmaster@web-t-sys.com>
* @file
* @package ExtraUtils/Login
*
*
*/

/*namespace PhangoApp\Framework\Libraries;

use PhangoApp\PhaView\View;
use PhangoApp\PhaI18n\I18n;
use PhangoApp\PhaModels\ModelForm;
use PhangoApp\PhaModels\CoreFields\PasswordField;*/

namespace PhangoApp\PhaLibs;

use PhangoApp\PhaModels\ModelForm;
use PhangoApp\PhaI18n\I18n;
use PhangoApp\PhaModels\CoreFields\PasswordField;
use PhangoApp\PhaView\View;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaRouter\Routes;
use PhangoApp\PhaLibs\Emailer;

class LoginClass {

	public $model_login;
	public $field_user;
	public $field_name='name';
	public $field_password;
	public $field_mail='email';
	public $field_recovery='token_recovery';
	public $name_cookie='';
	public $arr_user_session;
	public $arr_user_insert=array();
	public $field_key;
	static public $session;
	public $url_login='';
	public $url_insert='';
	public $url_recovery='';
	public $url_recovery_send='';
	public $login_view='common/user/standard/loginform';
	public $edit_fields=array();
	public $create_account_view='common/user/standard/insertuserform';
	public $recovery_pass_view='common/user/standard/recoverypassform';
	public $method_crypt='sha256';
	public $accept_conditions=1;
	public $was_prepared=0;
	public $cookie_path='';
	public $sender='';
	
	public function __construct($model_login, $field_user, $field_password, $field_key, $arr_user_session=array(), $arr_user_insert=array())
	{
	
		//Check is phangoUser the model.
		
		if(get_class($model_login)!='PhangoApp\PhaModels\ExtraModels\UserPhangoModel')
		{
			
			throw new \Exception('Special UserPhangoModel object is needed');
			
			die;
		
		}
		
		$this->model_login=&$model_login;
		$this->field_user=$field_user;
		$this->field_password=$field_password;
		$this->arr_user_session=$arr_user_session;
		$this->field_key=$field_key;
		$this->name_cookie=$this->model_login->name;
		$this->cookie_path=Routes::$root_url;
		
		$this->arr_user_insert=$arr_user_insert;
		
		$this->arr_user_insert[]=$this->field_user;
		$this->arr_user_insert[]=$this->field_password;
		$this->arr_user_insert[]='repeat_password';
		
		$this->arr_user_insert=array_unique($this->arr_user_insert, SORT_STRING);

		//Initialize form
		
		if(count($this->model_login->forms)==0)
		{
		
			$this->model_login->create_forms();
		
		}
		
		if(count($this->arr_user_session)==0)
		{
		
			$this->arr_user_session[]=$this->model_login->idmodel;
			$this->arr_user_session[]=$this->field_key;
		
		}

	}
	
	public function automatic_login($iduser)
	{
	
		$arr_user=$this->model_login->select_a_row($iduser, array($this->field_user, $this->field_password));
	
		return $this->login($arr_user[$this->field_user], $arr_user[$this->field_password], 0, 1);
	
	}
	
	public function login($user, $password, $no_expire_session=0, $yes_hash=0)
	{
		//load_libraries(array('fields/passwordfield'));
	
		$check_password=0;
	
		$user=Utils::form_text($user);
		
		$this->arr_user_session[]=$this->field_password;
		
		$this->model_login->set_conditions('where '.$this->field_user.'="'.$user.'"');
		
		$arr_user=$this->model_login->select_a_row_where($this->arr_user_session);
		
		settype($arr_user[$this->model_login->idmodel], 'integer');
		
		if($arr_user[$this->model_login->idmodel]==0)
		{
			
			ModelForm::set_values_form($this->model_login->forms, $_POST, 1);
		
			$this->model_login->forms[$this->field_password]->std_error= I18n::lang('users', 'user_error_nick_or_pass', 'Wrong user or password');
		
			unset($arr_user[$this->field_password]);
			
			return false;
		
		}
		else
		{
            
			$yes_password=0;
		
			
			if($yes_hash==0)
			{
			
				if(PasswordField::check_password($password, $arr_user[$this->field_password]))
				{
				
					$yes_password=1;
				
				}
				
			}
			else
			{
			
				if($password===$arr_user[$this->field_password])
				{
				
					$yes_password=1;
				
				}
				
			}
			
			if($yes_password==1)
			{
		
				unset($arr_user[$this->field_password]);
			
				LoginClass::$session=$arr_user;
				
				//Create token
				
				$new_token=sha1(Utils::get_token());
				
				$this->model_login->reset_require();
				
				if( $this->model_login->update(array($this->field_key => sha1($new_token)), 'where `'.$this->model_login->idmodel.'`='.$arr_user[$this->model_login->idmodel]) )
				{
					
					$this->model_login->reload_require();
					
					$lifetime=0;
					
					if($no_expire_session==1)
					{
						
						$lifetime=time()+31536000;
						
					
					}
					
					if(!setcookie(sha1($this->name_cookie), $new_token,$lifetime, $this->cookie_path))
					{
						
						return false;
					
					}
					//echo sha1($new_token); die;
					return true;
					
				}
				else
				{
				
					ModelForm::set_values_form($this->model_login->forms, $_POST, 1);
				
					return false;
				
				}
				
			}
			else
			{
				
				ModelForm::set_values_form($this->model_login->forms, $_POST, 1);
				
				$this->model_login->forms[$this->field_password]->std_error= I18n::lang('users', 'user_error_nick_or_pass', 'Wrong user or password');
			
				return false;
			
			}
		
		}
	
	}
	
	public function logout()
	{
	
		$_SESSION=array();
	
		session_destroy();
		
		setcookie(sha1($this->name_cookie), 0, 0, $this->cookie_path);
	
	}
	
	public function check_login()
	{
		
		$check_user=0;
		
		$cookie_val='';
		$cookie_name_sha1=sha1($this->name_cookie);
		
		if(isset($_COOKIE[$cookie_name_sha1]))
		{
		
			$cookie_val=sha1($_COOKIE[$cookie_name_sha1]);
		
			$check_user=1;
		
		}
		
		if($check_user==1)
		{
			
			$this->model_login->set_conditions('where '.$this->field_key.'="'.$cookie_val.'"');
			
			$arr_user=$this->model_login->select_a_row_where($this->arr_user_session);
			
			settype($arr_user[$this->model_login->idmodel], 'integer');
			
			if($arr_user[$this->model_login->idmodel]==0)
			{
			
				return false;
			
			}
			else
			{
                //$_SESSION[$this->model_login->idmodel]=$arr_user[$this->model_login->idmodel];
				LoginClass::$session=$arr_user;
			
				return true;
			
			}
		
		}
		else
		{
		
			
		
			return false;
				
		
		}
	
	}
	
	public function login_form()
	{
		
		echo View::load_view(array($this->model_login, $this), $this->login_view);
	
	}
	
	public function recovery_password_form()
	{
		
		echo View::load_view(array($this->model_login, $this), $this->recovery_pass_view);
	
	}
	
	public function recovery_password()
	{
        
		settype($_GET['token_recovery'], 'string');
		
		$_GET['token_recovery']=trim($this->model_login->check_where_sql($this->field_recovery, $_GET['token_recovery']));
		
		if($_GET['token_recovery']=='')
		{
		
			$email = @Utils::form_text( $_POST['email'] );
			
			$this->model_login->set_conditions('where '.$this->field_mail.'="'.$email.'"');
			
			$this->model_login->reset_conditions=false;
			
			$query=$this->model_login->select(array($this->model_login->idmodel, $this->field_name, $this->field_mail) );
			
			$this->model_login->reset_conditions=true;
			
			list($iduser_recovery, $nick, $email)=$this->model_login->fetch_row($query);
			
			settype($iduser_recovery, 'integer');
			
			if($iduser_recovery>0)
			{
			
				$email = @Utils::form_text( $_POST['email'] );
		
				$query=$this->model_login->select(array($this->model_login->idmodel, $this->field_name, $this->field_mail) );
				
				list($iduser_recovery, $nick, $email)=$this->model_login->fetch_row($query);
				
				settype($iduser_recovery, 'integer');
			
				//Create token recovery...
				
				$token_recovery=hash($this->method_crypt, Utils::get_token());
				
				$this->model_login->reset_require();
				
				$this->model_login->set_conditions('where '.$this->model_login->idmodel.'='.$iduser_recovery);
				
				$query=$this->model_login->update(array($this->field_recovery => $token_recovery));
				
				//$query=$model['recovery_password']->insert(array('iduser' => $iduser_recovery, 'token_recovery' => sha1($token_recovery), 'date_token' => TODAY) );
				
				//Send email
				
				$url_check_token=Routes::add_get_parameters($this->url_recovery_send, array('token_recovery' => $token_recovery));
				
				$topic_email =  I18n::lang('users', 'lost_name', 'You requested a new password');
				$body_email =  I18n::lang('users', 'hello_lost_pass', 'Hello, you have requested a new password.')."\n\n".
				I18n::lang('users', 'explain_code_pass', 'You have requested a new password. Copy and paste the following url into your browser, and a new password will be generated for you. If you did not request this operation, ignore this message.')
				."\n\n". 
				I18n::lang('users', 'copy_paste_code', 'Copy and paste the following url').': '.$url_check_token."\n\n". 
				I18n::lang('common', 'thanks', 'Thanks');
				
				if ( Emailer::send_mail($this->sender, $email, $topic_email, $body_email) )
				{
				
					echo '<p>'. I18n::lang('users', 'explain_email_code_pass', 'You have requested a new password. Copy and paste the following url into your browser, and a new password will be generated for you. If you did not request this operation, ignore this message.').'</p>';
				
				}
				else
				{
				
					echo '<p>'. I18n::lang('users', 'cannot_email_code_pass', 'We can not send to your email the instructions to change your password. Please contact the administrator of this site to solve the problem.').'</p>';
				
				}
				
			
			}
			else
			{

				echo  "<p>" .  I18n::lang('users', 'error_db_pass', 'Error, mail format is wrong').'</p>';
				
				echo  "<p><a href=\"".$this->url_recovery."\"><b>" . I18n::lang('common', 'go_back', 'Go back') . "</b></a></p>";

			}
		
		}
		else
		{

			$this->model_login->set_conditions('where '.$this->field_recovery.'="'.$_GET['token_recovery'].'"');
			
			$query=$this->model_login->select(array($this->model_login->idmodel, $this->field_name, $this->field_mail));
			
			list($iduser_recovery, $nick, $email)=$this->model_login->fetch_row($query);
			
			settype($iduser_recovery, 'integer');
			
			if($iduser_recovery>0)
			{

				$password=Utils::generate_random_password(); 
				
				$topic_email =  I18n::lang('users', 'success_change_password', 'The password was changed successfully.');
				$body_email =  I18n::lang('users', 'hello_lost_pass_successful', 'Hello, we have changed your password and is shown below. With these data should be back online in the system.')."\n\n".  
				I18n::lang('users', 'user_data', 'User\'s data') . "\n\n". 
				I18n::lang('users', 'user', 'User')." : $nick"."\n\n". 
				I18n::lang('common', 'email', 'Email')." : $email"."\n\n"  .  
				I18n::lang('users', 'new_pass', 'New password') . " : $password" . "\n\n" . 
				I18n::lang('common', 'thanks', 'Thanks');
					
				if ( $email !== "" )
				{
					
					//$query=$model['recovery_password']->delete('where '.$this->model_login->idmodel.'='.$iduser_recovery);

					$this->model_login->reset_require();
					
					$query = $this->model_login->update(array($this->field_password => $password, $this->field_recovery => ''), 'where '.$this->model_login->idmodel.'='.$iduser_recovery);
					
					if ( Emailer::send_mail($this->sender, $email, $topic_email, $body_email) )
					{
						
						echo  "<p>" .  I18n::lang('users', 'success_change_password', 'The password was changed successfully.').'</p>';
						echo  "<p>" .  I18n::lang('users', 'success_change_password_explain', 'We have sended to your email, the new password.').'</p>';

					} 
					else
					{

						echo  "<p>" .  I18n::lang('users', 'success_change_password', 'The password was changed successfully.').'</p>';
						
						echo  "<p>" .  I18n::lang('users', 'error_sending_mail_change_password', 'We can not send your new password to your email at this time, we are showing your user data in plain text. For added security, change your password once achieved identified in the system again.').'</p>';
						
						echo '<pre>';
						
						echo $body_email;
						
						echo '</pre>';

					} 
				} 

				else
				{

					echo  "<p>" .  I18n::lang('users', 'error_db_pass', 'Error, mail format is wrong').'</p>';

				}
				
			}
			else
			{
			
				echo  "<p>" .  I18n::lang('users', 'error_token_pass', 'Error: incorrect code used to change a password.').'</p>';
			
			}

			echo  "<p><a href=\"".$this->url_login. "</b></a></p>";
		}
	
	}
	
	public function create_account_form()
	{
		
		if($this->was_prepared==0)
		{
			
			$this->prepare_insert_user();
		
		}
	
		echo View::load_view(array('model' => $this->model_login, 'login_model' => $this), $this->create_account_view);
	
	}
	
	public function create_account()
	{
		
		$this->prepare_insert_user();
		
		$this->was_prepared=1;
					
		$post=Utils::filter_fields_array($this->arr_user_insert, $_POST);
		
		$no_user=0;
		
		$check_user=$this->model_login->components[$this->field_user]->check($post[$this->field_user]);
		
		//$no_user=$this->model_login->select_count('where `'.$this->model_login->name.'`.`'.$this->field_user.'`="'.$check_user.'"');
		
		// && $no_user==0
		
		/*if(ModelForm::check_form($this->model_login->forms, $post))
		{*/
            
			/*if($_POST['repeat_password']==$post[$this->field_password] && $check_captcha==1 && $no_user==0)
			{*/
			
			if($no_user===0)
            {
			
                $this->model_login->reset_require();
                
                foreach($this->arr_user_insert as $field_require)
                {
                
                    if(isset($this->model_login->components[$field_require]))
                    {
                        $this->model_login->components[$field_require]->required=1;
                    }
                }
                
                if($this->model_login->insert($post))
                {
                    
                    return true;
                
                }
                else
                {
                    
                    
                    ModelForm::set_values_form($this->model_login->forms, $_POST, 1);
                
                    return false;
                    
                }
            }
            else
            {
            
                $this->model_login->forms[$this->field_user]->std_error= I18n::lang('users', 'user_or_email_exists', 'User or email exists');
                
                
                ModelForm::set_values_form($this->model_login->forms, $_POST, 1);
        
                return false;
            
            }
            
		/*}
		else
		{
		
			if($no_user>0)
			{
				
				$this->model_login->forms[$this->field_user]->std_error= I18n::lang('users', 'user_or_email_exists', 'User or email exists');
			
			}
            
			ModelForm::set_values_form($this->model_login->forms, $_POST, 1);
		
			return false;
		
		}*/
	
	}
	
	public function prepare_insert_user()
	{
		
		$this->model_login->forms['repeat_password']=new \PhangoApp\PhaModels\Forms\PasswordForm('repeat_password');
		
		$this->model_login->forms['repeat_password']->label=I18n::lang('users', 'repeat_password', 'Repeat password');
		$this->model_login->forms['repeat_password']->required=1;
		//new ModelForm('repeat_password', 'repeat_password', 'PhangoApp\PhaModels\Coreforms::PasswordForm',  I18n::lang('users', 'repeat_password', 'Repeat password'), new PasswordField(), $required=1, $parameters='');
		
		if($this->accept_conditions==1)
		{
		
			//$this->model_login->forms['accept_conditions']=new ModelForm('form_login', 'accept_conditions', 'CheckBoxForm',  I18n::lang('users', 'accept_cond_register', 'Accept registration conditions'), new BooleanField(), $required=1, $parameters='');
			
			//$this->arr_user_insert[]='accept_conditions';
			
		}
	
	}
	
	public function obtain_cookie_token()
	{
	
		return $_COOKIE[sha1($this->name_cookie)];
	
	}
	
	
}

?>