<?php
class BackgroundSendEmailModel extends Model {

  public function addinfo($email, $title, $content, $status=1){
    $data['aid'] = session(C('USER_AUTH_KEY'));
    $data['email'] = $email;
    $data['title'] = $title;
    $data['content'] = $content;
    $data['sendtime'] = time();
    $data['status'] = $status;
    return $this -> add($data);
  }
}

