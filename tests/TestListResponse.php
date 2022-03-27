<?php

namespace ManeOlawale\RestResponse\Tests;

use ManeOlawale\RestResponse\AbstractListResponse;

class TestListResponse extends AbstractListResponse
{
    protected function getListArray(): array
    {
        return $this->responseArray['list'];
    }
}
