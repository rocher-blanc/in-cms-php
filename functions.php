<?php

function dd( ...$args )
{
	dump( ...$args );
	die;
}