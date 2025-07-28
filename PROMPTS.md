- this system now needs a notification system.
- both admins and users should be able to receive notifications.
- some of the actions that should trigger notifications include:
  - when a program is created
  - when a program is edited
  - when a submission is created/added
  - when a submission is edited
  - when a submission is deleted
  - when a program is deleted
  - when a submission is finalized

  some more specific actions:
  - when the user is assigned as the editor of a program.

- there is a legacy notification but it doesnt even work now so i need you to recreate a new one.
- this notification is going to be linked to email notifications but that is not a priority right now.
- the notification should include relevant information about the action that triggered it, such as the user involved, the program or submission affected, and a timestamp.
- the notification page should show comprehensive information about the notifications, allowing users to filter by type, date, or read/unread status.
- there's a notification table that i have created in the database, it has the following fields:

CREATE TABLE `notifications` (
	`notification_id` INT(10) NOT NULL AUTO_INCREMENT,
	`user_id` INT(10) NOT NULL,
	`message` TEXT NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`type` VARCHAR(50) NOT NULL DEFAULT 'update' COLLATE 'utf8mb4_0900_ai_ci',
	`read_status` TINYINT(3) NOT NULL DEFAULT '0',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`action_url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
	PRIMARY KEY (`notification_id`) USING BTREE,
	INDEX `user_id` (`user_id`) USING BTREE,
	CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_0900_ai_ci'
ENGINE=InnoDB
AUTO_INCREMENT=12
;

- you also can use the existing log audit system as a reference for how to "record" actions.
- create a test file for me to test the notification system.