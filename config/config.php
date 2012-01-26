<?php

/*
 * @WSDL
 * The absolute URL to your MinistryPlatform API file. Default is:
 * <your server>/ministryplatform/public/api.asmx?WSDL
 *
 */

		$this->wsdl = "https://ministryplatform.example.com/ministryplatform/public/api.asmx?WSDL";

/*
 * @API GUID
 * Your API GUID is located in the web.config file for any application that uses the API,
 * such as the Portal, Check-In, or CoreTools.
 *
 */

		$this->guid = ''; // API GUID

/*
 * @API PASSWORD
 * Your API password is located below your API GUID.
 *
 */

		$this->pw = ''; // API password

/*
 * @Server Name
 * This is the server name that you're connecting to. Usually this will be a
 * piece of the WSDL url listed above.
 */

		$this->servername = 'ministryplatform.example.com'; // server name

/*
 * @hours
 * Number of hours events to should be visible before *event start time*
 *
 */

		$this->hours = 24;

/*
 * @Security Role ID
 * Security role ID belonging to Group Check-In. Look this up after you run the installation SQL
 * file.
 *
 */

		$this->securityRoleID = 0; // this will never be 0. Change it please.

/* no ending ?> on purpose */