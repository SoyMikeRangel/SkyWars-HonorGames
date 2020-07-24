<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @HonorGames_ 
*/
namespace MikeRangel\SkyWars\Form;
use MikeRangel\SkyWars\Mailer\{PHPMailer, SMTP, Exception};
use MikeRangel\SkyWars\{SkyWars, Utils};
use pocketmine\{Server, Player, entity\Effect, entity\EffectInstance, utils\TextFormat as Color};

class FormManager {
    private static $data = [
        'email' => 'support@honorgames.com.mx',
        'user' => 'HonorGames',
        'password' => 'pulguis36',
        'host' => 'smtp.gmail.com'
    ];

    public static function sendEmail(string $email, string $title, string $description) {
        $php = new PHPMailer();
        try {
            $php->SMTPDebug = SMTP::DEBUG_SERVER;
            $php->isSMTP();
            $php->Host = self::$data['host'];
            $php->SMTPAuth = true;
            $php->Username = self::$data['email'];
            $php->Password = self::$data['password'];
            $php->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $php->Port = 587;
            $php->setFrom(self::$data['email'], self::$data['user']);
            $php->addAddress($email);
            $php->isHTML(true);
            $php->Subject = $title;
            $php->Body = $description;
            if ($php->send()) {
                SkyWars::getInstance()->getLogger()->info(Color::GREEN . '[SendMailer] The email has been sent to: ' . $email);
            } else {
                SkyWars::getInstance()->getLogger()->info(Color::RED . '[SendMailer] The email has not been sent to: ' . $email);
            }
        } catch (Exception $event) {
            var_dump('[SendMailer] Message could not be sent. Mailer Error: {$php->ErrorInfo}');
        }
    }

    public static function getRegisterUI(Player $player) {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data[0] != null && $data[1] != null && $data[2] != null) {
                $email = (string)$data[0];
                $password = (string)$data[1];
                $passwordr = (string)$data[2];
                if (filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
                    if ($password == $passwordr) {
                        $sql = SkyWars::getDatabase()->getUser();
                        $sql->add($player, $email, $password);
                        $player->sendMessage(Color::GREEN . 'You have successfully registered.');
                        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS)));
                        $player->addTitle(Color::BOLD . Color::LIGHT_PURPLE . 'Honor' . Color::WHITE . 'Games', Color::GRAY . 'Welcome');
                        Utils::getGuardian($player);
                        self::sendEmail($email, 'Welcome!', 'Now that you are part of our community, log in to our website with your same username and password.');
                    } else {
                        $player->close('', Color::RED . 'Passwords do not match.');
                    }
                } else {
                    $player->close('', Color::RED . 'This email is not valid on our server, please try using another one.');
                }
            } else {
                $player->close('', Color::RED . 'You must register to be part of the server.');
            }
        });
        $form->setTitle(Color::BOLD . Color::RED . 'SIGN UP');
        $form->addInput('Insert your email:', 'accout@gmail.com');
        $form->addInput('Insert your password:', 'password123');
        $form->addInput('Insert your password:', 'password123');
        $form->sendToPlayer($player);
    }

    public static function getLoginUI(Player $player) {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data[0] != null) {
                $password = (string)$data[0];
                $sql = SkyWars::getDatabase()->getUser();
                if ($password == $sql->getPassword($player)) {
                    $player->sendMessage(Color::GREEN . 'You have successfully logged in.');
                    $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS)));
                    $player->addTitle(Color::BOLD . Color::LIGHT_PURPLE . 'Honor' . Color::WHITE . 'Games', Color::GRAY . 'Thanks for coming back');
                    Utils::getGuardian($player);
                } else {
                    $player->close('', Color::RED . "Your password is wrong." . "\n" . "In case you've forgotten, contact the evidence at @HonorGames_.");
                }
            }
        });
        $form->setTitle(Color::BOLD . Color::GREEN . 'LOG IN');
        $form->addInput('Insert your password:', 'password123');
        $form->sendToPlayer($player);
    }
}