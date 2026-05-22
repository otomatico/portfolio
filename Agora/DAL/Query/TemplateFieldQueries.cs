namespace Agora.DAL.Query
{
    class TemplateFieldQueries
    {
        public const string GetField = "SELECT * FROM template_field WHERE IdTemplate =  @IdTemplate ORDER BY Place";
        public const string InsertField = @"INSERT INTO template_field (Name, AttributeName, Required, Place, IdTemplate, IdType, IdMaster, DefaultValue) VALUES (@Name, @AttributeName, @Required, @Place, @IdTemplate, @IdType, @IdMaster, @DefaultValue);";
        public const string UpdateField = @"UPDATE template_field SET Name = @Name, AttributeName= @AttributeName, Required=@Required, Place= @Place, IdTemplate = @IdTemplate, IdType = @IdType, IdMaster = @IdMaster, DefaultValue=@DefaultValue WHERE IdField = @IdField;";
        public const string DeleteField = @"DELETE FROM template_field  WHERE IdField = @IdField;";
        public const string DeleteFieldByIdTemplate = @"DELETE FROM template_field  WHERE IdTemplate = @IdTemplate;";
    }
}