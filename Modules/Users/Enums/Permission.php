<?php

namespace Modules\Users\Enums;

enum Permission: string
{
    case  USER_CREATE = 'user.create';
    case  USER_UPDATE = 'user.update';
    case  USER_DELETE = 'user.delete';
    case  USER_READ = 'user.read';

    //Comments
    case  COMMENT_READ = 'comment.read';
    case  COMMENT_CREATE = 'comment.create';
    case  COMMENT_UPDATE = 'comment.update';
    case  COMMENT_DELETE = 'comment.delete';
}
