namespace Agora.DAL.Query
{
    class MasterQueries
    {
        public const string GetMaster = "SELECT * FROM master ORDER BY ModifyDate DESC";
        public const string InsertMaster = @"INSERT INTO master (Name) VALUES (@Name);";
        public const string UpdateMaster = @"UPDATE master SET Name = @Name, ModifyDate = @ModifyDate WHERE IdMaster = @IdMaster;";
        public const string DeleteMaster = @"DELETE FROM master  WHERE IdMaster = @IdMaster;";
        public const string GetOptions = "SELECT * FROM master_option WHERE idMaster = @IdMaster ORDER BY Place";
        public const string InsertOption = "INSERT INTO master_option (IdMaster,Name, Value, Place) VALUES ( @IdMaster, @Name, @Value, @Place)";
        public const string UpdatetOption = "UPDATE master_option SET Name = @Name, Value =  @Value, Place = @Place WHERE idOption = @IdOption";
        public const string DeleteOptionByIdOption = @"DELETE FROM master_option WHERE IdOption = @IdOption;";
        public const string DeleteOptionByIdMaster = @"DELETE FROM master_option WHERE IdMaster = @IdMaster;";
    }
}