<?php

class LogController extends MiniEngine_Controller
{
    public function indexAction()
    {
        return $this->json(PostLog::search(1)->toArray());
    }

    public function actorAction()
    {
        return $this->json(Actor::search(1)->toArray());
    }
}
