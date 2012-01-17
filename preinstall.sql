IF ((SELECT COUNT([Role_ID])
 FROM dp_Roles
 WHERE Role_Name='GroupCheckIn') = 0)
BEGIN
	INSERT INTO dp_Roles
			   ([Role_Name]
			   ,[Domain_ID]
			   ,[Mass_Email_Quota]
			   ,[_AdminRole])
		 VALUES
			   ('GroupCheckIn'
			   ,1
			   ,NULL
			   ,0)


	INSERT INTO dp_User_Roles
			   ([User_ID]
			   ,[Role_ID]
			   ,[Domain_ID])
	SELECT UR.User_ID
			,(SELECT [Role_ID]
	  FROM dp_Roles
	  WHERE Role_Name='GroupCheckIn') AS RoleID
		  ,UR.Domain_ID
	  FROM dp_User_Roles UR
	  LEFT JOIN dp_Roles AS R ON R.Role_ID = UR.Role_ID
	  LEFT JOIN dp_Users AS U ON U.User_ID = UR.User_ID
	  WHERE Role_Name='Administrators'

END
GO

