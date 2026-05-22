namespace Agora.DAL.Query
{
    class TemplateQueries
    {
        public const string GetTemplate = "SELECT * FROM template";
        public const string InsertTemplate = @"INSERT INTO template (Name) VALUES (@Name);";
        public const string UpdateTemplate = @"UPDATE template SET Name = @Name, ModifyDate = @ModifyDate WHERE IdTemplate = @IdTemplate;";
        public const string DeleteTemplate = @"DELETE FROM template  WHERE IdTemplate = @IdTemplate;";
    }
}