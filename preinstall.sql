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

/****** Object:  StoredProcedure [dbo].[api_GroupCheckIn_GetCheckInEvents]    Script Date: 01/09/2012 15:38:29 ******/IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[api_GroupCheckIn_GetCheckInEvents]') AND type in (N'P', N'PC'))DROP PROCEDURE [dbo].[api_GroupCheckIn_GetCheckInEvents]GO/****** Object:  StoredProcedure [dbo].[api_GroupCheckIn_GetGroupMembers]    Script Date: 01/09/2012 15:38:29 ******/IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[api_GroupCheckIn_GetGroupMembers]') AND type in (N'P', N'PC'))DROP PROCEDURE [dbo].[api_GroupCheckIn_GetGroupMembers]GO/****** Object:  StoredProcedure [dbo].[api_GroupCheckIn_GetGroupsByActivity]    Script Date: 01/09/2012 15:38:30 ******/IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[api_GroupCheckIn_GetGroupsByActivity]') AND type in (N'P', N'PC'))DROP PROCEDURE [dbo].[api_GroupCheckIn_GetGroupsByActivity]GO/****** Object:  StoredProcedure [dbo].[api_GroupCheckIn_GetCheckInEvents]    Script Date: 01/09/2012 15:38:30 ******/SET ANSI_NULLS ONGOSET QUOTED_IDENTIFIER ONGOCREATE PROCEDURE [dbo].[api_GroupCheckIn_GetCheckInEvents]	@DomainID int	,@ShowEventsFromTime datetime	,@ShowEventsThroughTime DateTimeASBEGIN	DECLARE @DefaultEarlyCheckIn smallint	DECLARE @DefaultLateCheckIn smallint	SET @DefaultEarlyCheckIn = 240 -- 4 hours	SET @DefaultLateCheckIn = 30 -- 1/2 hour	SELECT		Event_ID AS RecordID		,RIGHT(CONVERT(varchar(25), Event_Start_Date, 100),7) + ' ' + Event_Title + ISNULL(' | ' + M.Ministry_Name + ISNULL(' | ' + Cong.Congregation_Name,''),'') AS RecordDescription		,Event_Title		,Congregation_Name		,Ministry_Name		,Prog.[Program_Name]		,Event_Start_Date		,Event_End_Date		,DATEADD(n,ISNULL([Early_Check-in_Period],@DefaultEarlyCheckIn)*-1,Event_Start_Date) AS EarlyCheckinStart		,DATEADD(n,ISNULL([Late_Check-in_Period],@DefaultLateCheckIn),Event_Start_Date) AS LateCheckinStop	FROM Events	 INNER JOIN Programs Prog ON Prog.Program_ID = Events.Program_ID	 INNER JOIN Ministries M ON M.Ministry_ID = Prog.Ministry_ID	 LEFT OUTER JOIN Congregations Cong ON Cong.Congregation_ID = Events.Congregation_ID	WHERE Events.Domain_ID = @DomainID	--	AND (Events.Congregation_ID = @CongregationID OR @CongregationID = 0)	--	AND (Prog.Ministry_ID = @MinistryID OR @MinistryID = 0)		AND Events.[Allow_Check-in] = 1		AND (@ShowEventsFromTime BETWEEN DATEADD(n,ISNULL([Early_Check-in_Period],@DefaultEarlyCheckIn)*-1,Event_Start_Date) AND Event_End_Date OR Event_Start_Date BETWEEN @ShowEventsFromTime  AND @ShowEventsThroughTime)--		AND DATEADD(n,ISNULL([Early_Check-in_Period],@DefaultEarlyCheckIn)*-1,Event_Start_Date) <= @LocalTime--		AND @LocalTime < Event_End_Date--		AND DATEADD(n,ISNULL([Late_Check-in_Period],@DefaultLateCheckIn),Event_Start_Date) >= @LocalTime	ORDER BY Event_Start_Date	ENDGO/****** Object:  StoredProcedure [dbo].[api_GroupCheckIn_GetGroupMembers]    Script Date: 01/09/2012 15:38:30 ******/SET ANSI_NULLS ONGOSET QUOTED_IDENTIFIER ONGOCREATE PROCEDURE [dbo].[api_GroupCheckIn_GetGroupMembers]	@DomainID int	,@EventID int	,@GroupID INTASBEGIN	SELECT C.Display_Name	, C.Contact_ID	, P.Participant_ID	, EP.Event_Participant_ID 	, EP.Participation_Status_ID 		, GP.Group_Participant_ID	, GP.Group_ID	, GR.Role_Title 	, GRT.Group_Role_Type 	, EP.Event_ID	, EP.Time_In 	, EP.Time_Out 		FROM Group_Participants GP		INNER JOIN Participants P ON P.Participant_ID = GP.Participant_ID 		INNER JOIN Contacts C ON C.Contact_ID = P.Contact_ID		INNER JOIN Group_Roles GR ON GR.Group_Role_ID = GP.Group_Role_ID 		INNER JOIN Group_Role_Types GRT ON GRT.Group_Role_Type_ID = GR.Group_Role_Type_ID 		LEFT OUTER JOIN Event_Participants EP ON EP.Group_Participant_ID = GP.Group_Participant_ID AND EP.Event_ID = @EventID 			--may need an outer apply statmeent for EP join in case there is more than one record	WHERE GP.Group_ID = @GroupID	 AND GP.Domain_ID = @DomainID	 AND GETDATE() BETWEEN GP.Start_Date AND ISNULL(GP.End_Date,GETDATE())	UNION	SELECT C.Display_Name	, C.Contact_ID	, P.Participant_ID	, EP.Event_Participant_ID 	, EP.Participation_Status_ID 	, NULL AS Group_Participant_ID	, 0 AS Group_ID	, RIGHT(PS.Participation_Status,DATALENGTH(PS.Participation_Status)-3) AS Role_Title	, 'Participant' AS Role_Type	, E.Event_ID	, EP.Time_In 	, EP.Time_Out 	FROM Events E		INNER JOIN Event_Participants EP ON EP.Event_ID = E.Event_ID AND EP.Group_Participant_ID IS NULL --AND EP.Participation_Status_ID IN (1,2)		INNER JOIN Participation_Statuses PS ON PS.Participation_Status_ID = EP.Participation_Status_ID 		INNER JOIN Participants P ON P.Participant_ID = EP.Participant_ID		INNER JOIN Contacts C ON C.Contact_ID = P.Contact_ID 	WHERE E.Event_ID = @EventID AND E.Domain_ID = @DomainID AND @GroupID = 0	ORDER BY Display_Name ENDGO/****** Object:  StoredProcedure [dbo].[api_GroupCheckIn_GetGroupsByActivity]    Script Date: 01/09/2012 15:38:30 ******/SET ANSI_NULLS ONGOSET QUOTED_IDENTIFIER ONGOCREATE PROCEDURE [dbo].[api_GroupCheckIn_GetGroupsByActivity]	@DomainID int	,@EventID intASBEGIN	SELECT E.Event_Title, E.Prohibit_Guests, G.Group_ID AS Record_ID, G.Group_Name AS [Description] 	FROM Events E		INNER JOIN Event_Groups EG ON EG.Event_ID = E.Event_ID		INNER JOIN Groups G ON G.Group_ID = EG.Group_ID	WHERE E.Event_ID = @EventID AND E.Domain_ID = @DomainID	UNION	SELECT E.Event_Title, E.Prohibit_Guests, 0 AS Record_ID, '*Registered - No Group' AS [Description] 	FROM Events E	WHERE E.Event_ID = @EventID AND E.Domain_ID = @DomainID	ORDER BY [Description]ENDGO