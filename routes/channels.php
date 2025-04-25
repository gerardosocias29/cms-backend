<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('department_{id}', function ($user, $id) {
  // Replace this with your own access logic
  return true;
});
