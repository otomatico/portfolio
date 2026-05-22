CREATE DATABASE agora
USE [agora]
GO
/****** Object:  Table [dbo].[master]    Script Date: 25/09/2023 12:30:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[master](
	[IdMaster] [uniqueidentifier] NOT NULL,
	[Name] [varchar](100) NOT NULL,
	[CreateDate] [datetime] NOT NULL,
	[ModifyDate] [datetime] NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[master_option]    Script Date: 25/09/2023 12:30:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[master_option](
	[IdOption] [uniqueidentifier] NOT NULL,
	[IdMaster] [uniqueidentifier] NOT NULL,
	[Name] [varchar](100) NOT NULL,
	[Value] [varchar](100) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[node]    Script Date: 25/09/2023 12:30:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[node](
	[IdNode] [uniqueidentifier] NULL,
	[IdTemplate] [uniqueidentifier] NULL,
	[Name] [varchar](100) NULL,
	[JsonValue] [varchar](255) NULL,
	[ModifyDate] [datetime] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[node_relation]    Script Date: 25/09/2023 12:30:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[node_relation](
	[IdRelacion] [int] IDENTITY(1,1) NOT NULL,
	[IdNode] [uniqueidentifier] NOT NULL,
	[IdNodeParent] [uniqueidentifier] NOT NULL,
	[IdNodeRoot] [uniqueidentifier] NOT NULL,
	[Order] [int] NOT NULL,
 CONSTRAINT [PK_node_relation] PRIMARY KEY CLUSTERED 
(
	[IdRelacion] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[template]    Script Date: 25/09/2023 12:30:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[template](
	[IdTemplate] [uniqueidentifier] NOT NULL,
	[Name] [nvarchar](100) NULL,
	[ModifyDate] [datetime] NOT NULL,
 CONSTRAINT [PK_template] PRIMARY KEY CLUSTERED 
(
	[IdTemplate] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[template_allowed_children]    Script Date: 25/09/2023 12:30:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[template_allowed_children](
	[IdTemplate] [uniqueidentifier] NOT NULL,
	[IdTemplateParent] [uniqueidentifier] NOT NULL,
	[MaxAllowed] [int] NULL,
 CONSTRAINT [PK_template_allowed_children] PRIMARY KEY CLUSTERED 
(
	[IdTemplate] ASC,
	[IdTemplateParent] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[template_field]    Script Date: 25/09/2023 12:30:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[template_field](
	[IdField] [uniqueidentifier] NOT NULL,
	[IdTemplate] [uniqueidentifier] NULL,
	[IdMaster] [uniqueidentifier] NULL,
	[IdType] [int] NULL,
	[Name] [varchar](100) NULL,
	[AttributeName] [varchar](100) NULL,
	[DefaultValue] [varchar](100) NULL,
	[Required] [tinyint] NULL,
	[Order] [int] NULL,
 CONSTRAINT [PK_template_field] PRIMARY KEY CLUSTERED 
(
	[IdField] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[type_field]    Script Date: 25/09/2023 12:30:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[type_field](
	[IdType] [int] NULL,
	[Field] [varchar](20) NULL
) ON [PRIMARY]
GO
INSERT INTO type_field ([IdType], [Field]) VALUES
	(1, 'Boleano'),
	(2, 'Numero'),
	(3, 'Texto'),
	(4, 'Seleccion'),
	(5, 'Seleccion Multiple'),
	(6, 'Recurso');
GO
ALTER TABLE [dbo].[master] ADD  CONSTRAINT [DF_Master_IdMaster]  DEFAULT (newid()) FOR [IdMaster]
GO
ALTER TABLE [dbo].[master] ADD  CONSTRAINT [DF_Master__CreateDate]  DEFAULT (getdate()) FOR [CreateDate]
GO
ALTER TABLE [dbo].[master] ADD  CONSTRAINT [DF_Master__ModifyDate]  DEFAULT (getdate()) FOR [ModifyDate]
GO
ALTER TABLE [dbo].[master_option] ADD  CONSTRAINT [DF_MasterOption_IdOption]  DEFAULT (newid()) FOR [IdOption]
GO
ALTER TABLE [dbo].[node_relation] ADD  CONSTRAINT [DF_node_relation_Order]  DEFAULT ((0)) FOR [Order]
GO
ALTER TABLE [dbo].[template] ADD  CONSTRAINT [DF_template_ModifyDate]  DEFAULT (getdate()) FOR [ModifyDate]
GO
ALTER TABLE [dbo].[template_field] ADD  CONSTRAINT [DF_template_field_Required]  DEFAULT ((0)) FOR [Required]
GO
ALTER TABLE [dbo].[template_field] ADD  CONSTRAINT [DF_template_field_Order]  DEFAULT ((0)) FOR [Order]
GO
ALTER TABLE [dbo].[template_field]  WITH CHECK ADD  CONSTRAINT [FK_template_field_template_field] FOREIGN KEY([IdField])
REFERENCES [dbo].[template_field] ([IdField])
GO
ALTER TABLE [dbo].[template_field] CHECK CONSTRAINT [FK_template_field_template_field]
GO

